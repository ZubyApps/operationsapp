<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entity\Expense;
use App\Entity\Job;
use App\ResponseFormatter;
use App\Services\ReportService;
use App\Services\RequestService;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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

    public function jobTypeIndex(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'reports/jobtype/jobtype.twig');
    }

    public function loadJobTypeReports(Request $request, Response $response): Response
    {

        $params = 1;
        $jobs   = $this->reportService->jobReportsByMonth($request->getQueryParams());

        $transformer = function (Job $job) {
            return [
                'jobType'       => $job->getJobType()->getName(),
                'bill'          => $job->getAmountDue(),
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

    public function loadJobListByDate(Request $request, Response $response): Response
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

    public function expensesIndex(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'reports/expenses/expenses.twig');
    }

    public function loadExpenseReports(Request $request, Response $response): Response
    {

        $params = 1;
        $expenses   = $this->reportService->expenseReportsByMonth($request->getQueryParams());

        $transformer = function (Expense $expense) {
            return [
                'category'       => $expense->getCategory()->getName(),
                'amount'         => $expense->getAmount(),
            ];
        };

        $expenseList = array_map($transformer, (array) $expenses->getIterator());
        $countsArray =  \array_count_values(\array_column($expenseList, 'category'));
        $totalExpenses = count($expenses);

        $finalReport = [];
        if (!empty($expenseList)) {
            foreach ($expenseList as $expense) {
                $preparedTotalsArray[] = [$expense['category'] => [$expense['category'], $expense['amount']]];
            }

            $totalAmountsArray = array_reduce(
                $preparedTotalsArray,
                function ($carry, $expense) {

                    foreach ($expense as $category => $value) {

                        if (array_key_exists($category, $carry)) {
                            $carry[$category] += $value[1];
                        } else {
                            $carry[$category] = $value[1];
                        }
                    }
                    return  $carry;
                },
                []
            );


            $preparedCategoriesArray = \array_flip(\array_keys($countsArray));

            foreach ($preparedCategoriesArray as $key => $value) {
                $categoriesArray[$key] = $key;
            }

            $mergedArray = \array_merge_recursive($categoriesArray, $totalAmountsArray, $countsArray);

            $num = 0;
            foreach ($mergedArray as $key => $value) {
                $transformedKeysMergedArray[] = $jobTypeFinal[$num++] = $value;
            }

            foreach ($transformedKeysMergedArray as $value) {

                $finalReport[] = [
                    'category'      => $value[0],
                    'totalAmount'   => $value[1],
                    'count'         => $value[2],

                ];
            }
        }

        return $this->responseFormatter->asDataTable(
            $response,
            \array_merge_recursive($finalReport),
            $params,
            $totalExpenses
        );
    }

    public function loadExpenseListByDate(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $expenses           = $this->reportService->listExpensesByDateAndCategory($request->getQueryParams());
        $transformer    = function (Expense $expense) {
            return [
                'createdAt'         => $expense->getCreatedAt()->format('d-M-y'),
                'sponsor'           => $expense->getSponsor()->getName(),
                'amount'            => $expense->getAmount(),
                'description'       => $expense->getDescription(),
                'dateSpent'         => $expense->getDate()->format('d-M-y'),
            ];
        };

        $totalExpenses = count($expenses);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $expenses->getIterator()),
            $params->draw,
            $totalExpenses
        );
    }

    public function profitLossIndex(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'reports/profit_loss/profit_loss.twig');
    }

    public function loadProfitLossReports(Request $request, Response $response): Response
    {
        $params = 1;
        $jobs   = $this->reportService->jobReportsByMonth($request->getQueryParams());
        $expenses   = $this->reportService->expenseReportsByMonth($request->getQueryParams());

        $billsTransformer = function (Job $job) {
            return $job->getAmountDue();
        };

        $paymentsTransformer = function (Job $job) {
            return $job->getPaymentsTotal();
        };

        $expensesTransformer = function (Expense $expense) {
            return $expense->getAmount();
        };

        $totalBills = array_map($billsTransformer, (array) $jobs->getIterator());
        $totalPayments = array_map($paymentsTransformer, (array) $jobs->getIterator());
        $totalExpenses = array_map($expensesTransformer, (array) $expenses->getIterator());

        $finalArray[] = [
            'totalBills'    => array_sum($totalBills),
            'totalPayments' => array_sum($totalPayments),
            'totalExpenses' => array_sum($totalExpenses),
        ];

        $totalArray = count($finalArray);

        return $this->responseFormatter->asDataTable(
            $response,
            $finalArray,
            $params,
            $totalArray
        );
    }
}
