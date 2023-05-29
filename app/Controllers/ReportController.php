<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\DataTableQueryParams;
use App\Entity\Job;
use App\Entity\JobType;
use App\RequestValidators\DateRequestValidator;
use App\RequestValidators\RequestValidatorFactory;
use App\ResponseFormatter;
use App\Services\ReportService;
use App\Services\RequestService;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use function DI\string;

class ReportController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestService $requestService,
        private readonly ReportService $reportService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory
    ) {
    }
    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'reports/index.twig');
    }

    public function load(Request $request, Response $response)
    {

        $params = 1;
        $jobs   = $this->reportService->jobReportsByMonth($request->getQueryParams());

        $transformer = function (Job $job) {
            return [
                'jobType'       => $job->getJobType()->getName(),
                'bill'        => $job->getAmountDue(),
                'paid'          => $job->getPaymentsTotal()
            ];
        };

        $joblist = array_map($transformer, (array) $jobs->getIterator());
        $countArray =  \array_count_values(\array_column($joblist, 'jobType'));
        $totalJobs = count($jobs);

        $finalReport = [];
        if (!empty($joblist)) {
            foreach ($joblist as $job) {
                $preparedTotalsArray[] = [$job['jobType'] => [$job['jobType'], $job['bill'], $job['paid']]];
            }

            $totalBillsArray = array_reduce(
                $preparedTotalsArray,
                function ($carry, $job) {

                    foreach ($job as $type => $score) {

                        if (array_key_exists($type, $carry)) {
                            $carry[$type] += $score[1];
                        } else {
                            $carry[$type] = $score[1];
                        }
                    }
                    return  $carry;
                },
                []
            );

            $totalPaidArray = array_reduce(
                $preparedTotalsArray,
                function ($carry, $job) {

                    foreach ($job as $type => $score) {

                        if (array_key_exists($type, $carry)) {
                            $carry[$type] += $score[2];
                        } else {
                            $carry[$type] = $score[2];
                        }
                    }
                    return  $carry;
                },
                []
            );

            $preparedJobtypesArray = \array_flip(\array_keys($countArray));

            foreach ($preparedJobtypesArray as $key => $value) {
                $jobTypesArray[$key] = $key;
            }

            $mergedArray = \array_merge_recursive($jobTypesArray, $totalBillsArray, $totalPaidArray, $countArray);

            $num = 0;
            foreach ($mergedArray as $key => $value) {
                $transformedKeysMergedArray[] = $jobTypeFinal[$num++] = $value;
            }

            foreach ($transformedKeysMergedArray as $value) {

                $finalReport[] = [
                    'jobType'   => $value[0],
                    'totalBill' => $value[1],
                    'totalPaid' => $value[2],
                    'count'     => $value[3],

                ];
            }
        }

        return $this->responseFormatter->asDataTable(
            $response,
            \array_merge_recursive($finalReport),
            $params,
            $totalJobs
        );
    }

    public function loadListByDate(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $jobs           = $this->reportService->listJobsByDateAndType($request->getQueryParams());
        $transformer    = function (Job $job) {
            return [
                'date'              => $job->getCreatedAt()->format('d-M-y'),
                'client'            => $job->getClient()->getName(),
                'bill'              => $job->getAmountDue(),
                'paid'              => $job->getPaymentsTotal(),
                'jobStatus'         => $job->getJobStatus(),
            ];
        };

        $totaljobs = count($jobs);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $jobs->getIterator()),
            $params->draw,
            $totaljobs
        );
    }
}
