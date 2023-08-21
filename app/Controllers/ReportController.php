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

    public function jobReportIndex(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'reports/jobtype/jobtype.twig');
    }

    public function loadJobTypesByIntervals(Request $request, Response $response):Response
    {

        $params = 1;
        $jobs   = $this->reportService->getJobTypesByIntervals($request->getQueryParams());

        $totalJobs = count($jobs);
        
        return $this->responseFormatter->asDataTable(
            $response,
            $jobs,
            $params,
            $totalJobs
        );
    }

    public function loadJobsByDate(Request $request, Response $response): Response
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

    public function expenseReportIndex(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'reports/expenses/expenses.twig');
    }

    public function loadExpenseReports(Request $request, Response $response): Response
    {

        $params = 1;
        $expenses   = $this->reportService->expenseReportsByMonth($request->getQueryParams());

        $totalExpenses = count($expenses);

        return $this->responseFormatter->asDataTable(
            $response,
            $expenses,
            $params,
            $totalExpenses
        );
    }

    public function loadExpenseListByDate(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $expenses       = $this->reportService->listExpensesByDateAndCategory($request->getQueryParams());
        $transformer    = function (Expense $expense) {
            return [
                'sponsor'           => $expense->getSponsor()->getName(),
                'amount'            => $expense->getAmount(),
                'description'       => $expense->getDescription(),
                'dateSpent'         => $expense->getDate()->format('dS M y'),
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
        $job   = $this->reportService->getJobTotals($request->getQueryParams());
        $expense  = $this->reportService->getExpenseTotals($request->getQueryParams());

        $finalArray[] = [
            'totalBills'    => $job['totalBill'],
            'totalPayments' => $job['totalPaid'],
            'totalExpenses' => $expense['totalExpense'],
        ];

        $totalArray = count($finalArray);

        return $this->responseFormatter->asDataTable(
            $response,
            $finalArray,
            $params,
            $totalArray
        );
    }

    public function yearIndex(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'reports/yearly/yearly_reports.twig');
    }

    public function loadYearlyJobs(Request $request, Response $response):Response
    {
        $params = 1;
        $jobs   = $this->reportService->getMonthlyJobsSummary($request->getQueryParams());

        $totalJobs = count($jobs);

        return $this->responseFormatter->asDataTable(
            $response,
            $jobs,
            $params,
            $totalJobs
        );
    }

    public function loadJobsByMonth(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $jobs           = $this->reportService->getJobsByMonth($request->getQueryParams());

        $totaljobs = count($jobs);

        return $this->responseFormatter->asDataTable(
            $response,
            $jobs,
            $params->draw,
            $totaljobs
        );
    }

    public function loadYearlyExpenses(Request $request, Response $response):Response
    {
        $params = 1;
        $expenses   = $this->reportService->getMonthlyExpensesSummary($request->getQueryParams());

        $totalExpenses = count($expenses);

        return $this->responseFormatter->asDataTable(
            $response,
            $expenses,
            $params,
            $totalExpenses
        );
    }

    public function loadExpensesByMonth(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $expenses       = $this->reportService->getExpensesByMonth($request->getQueryParams());

        $totalExpenses = count($expenses);

        return $this->responseFormatter->asDataTable(
            $response,
            $expenses,
            $params->draw,
            $totalExpenses
        );
    }

    public function loadYearlyIncome(Request $request, Response $response): Response
    {
        $params     = 1;
        $jobs       = $this->reportService->getMonthlyJobsSummary($request->getQueryParams());
        $expenses   = $this->reportService->getMonthlyExpensesSummary($request->getQueryParams());

        $incomeArray = [...$jobs, ...$expenses];

        $months = [
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'January', 'm' => 1],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'February', 'm' => 2],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'March', 'm' => 3],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'April', 'm' => 4],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'May', 'm' => 5],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'June', 'm' => 6],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'July', 'm' => 7],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'August', 'm' => 8],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'September', 'm' => 9],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'October', 'm' => 10],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'November', 'm' => 11],
            ['bill' => 0, 'paid' => 0,'expense' => 0, 'month_name' => 'December', 'm' => 12],
        ];


        foreach($incomeArray as $income){
            foreach($months as $key => $month){
                if ($month['m'] === $income['month']){
                    $months[$key]['bill'] === 0 && $income['bill'] ? $months[$key]['bill'] = $income['bill'] : 0 ;

                    $months[$key]['paid'] === 0 && $income['paid'] ? $months[$key]['paid'] = $income['paid'] : 0 ;
                    
                    $months[$key]['expense'] = $income['amount'] ?? 0;
                }
            }
        }

        $total = count($months);

        return $this->responseFormatter->asDataTable(
            $response,
            $months,
            $params,
            $total
        );
    }
}
