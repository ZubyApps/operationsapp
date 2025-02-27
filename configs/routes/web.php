<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\ClientController;
use App\Controllers\DepartmentController;
use App\Controllers\ExpenseController;
use App\Controllers\HomeController;
use App\Controllers\JobController;
use App\Controllers\JobTypeController;
use App\Controllers\PaymentController;
use App\Controllers\PayMethodController;
use App\Controllers\PayStatusController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;
use App\Controllers\SponsorController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RegisterMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', [HomeController::class, 'index'])->add(AuthMiddleware::class);

    
    $app->group('', function (RouteCollectorProxy $guest) {
        $guest->get('/login', [AuthController::class, 'loginView']);
        $guest->post('/login', [AuthController::class, 'logIn']);
    })->add(GuestMiddleware::class);
    
    $app->post('/logout', [AuthController::class, 'logOut'])->add(AuthMiddleware::class);
    
    $app->group('/register', function (RouteCollectorProxy $register) {
        $register->get('', [AuthController::class, 'registerView'])->add(RegisterMiddleware::class);
        $register->post('', [AuthController::class, 'register']);
    })->add(AuthMiddleware::class);

    $app->group('/reports', function (RouteCollectorProxy $reports) {
        $reports->get('/job_reports', [ReportController::class, 'jobReportIndex']);
        $reports->get('/load/job_reports', [ReportController::class, 'loadJobTypeSByIntervals']);
        $reports->get('/load/listjobtypes', [ReportController::class, 'loadJobsByDate']);
        $reports->get('/expense_reports', [ReportController::class, 'expenseReportIndex']);
        $reports->get('/load/expenses', [ReportController::class, 'loadExpenseReports']);
        $reports->get('/load/listexpenses', [ReportController::class, 'loadExpenseListByDate']);
        $reports->get('/profit_report', [ReportController::class, 'profitLossIndex']);
        $reports->get('/load/profit_loss', [ReportController::class, 'loadProfitLossReports']);
        $reports->get('/yearly_reports', [ReportController::class, 'yearIndex']);
        $reports->get('/load/yearlyJobs', [ReportController::class, 'loadYearlyJobs']);
        $reports->get('/load/yearlyExpenses', [ReportController::class, 'loadYearlyExpenses']);
        $reports->get('/load/jobs_by_month', [ReportController::class, 'loadJobsByMonth']);
        $reports->get('/load/expenses_by_month', [ReportController::class, 'loadExpensesByMonth']);
        $reports->get('/load/yearlyIncome', [ReportController::class, 'loadYearlyIncome']);
    })->add(RegisterMiddleware::class)->add(AuthMiddleware::class);

    $app->group('/clients', function (RouteCollectorProxy $client) {
        $client->get('', [ClientController::class, 'index']);
        $client->get('/load', [ClientController::class, 'load']);
        $client->post('', [ClientController::class, 'store']);
        $client->get('/list', [ClientController::class, 'clientList']);
        $client->delete('/{id:[0-9]+}', [ClientController::class, 'delete']);
        $client->get('/{id:[0-9]+}', [ClientController::class, 'get']);
        $client->get('/details/{id:[0-9]+}', [ClientController::class, 'getDetails']);
        $client->post('/{id:[0-9]+}', [ClientController::class, 'update']);
    })->add(AuthMiddleware::class);

    $app->group('/jobs', function (RouteCollectorProxy $job) {
        $job->get('', [JobController::class, 'index']);
        $job->get('/load', [JobController::class, 'load']);
        $job->get('/load/details', [JobController::class, 'loadJobDetails']);
        $job->get('/load/booked', [JobController::class, 'loadBookedJobs']);
        $job->get('/load/inprogress', [JobController::class, 'loadJobsInProgress']);
        $job->post('', [JobController::class, 'store']);
        $job->get('/list', [JobController::class, 'jobList']);
        $job->delete('/{id:[0-9]+}', [JobController::class, 'delete']);
        $job->get('/{id:[0-9]+}', [JobController::class, 'get']);
        $job->get('/details/{id:[0-9]+}', [JobController::class, 'getJobDetails']);
        $job->post('/{id:[0-9]+}', [JobController::class, 'update']);
        $job->post('/status/{id:[0-9]+}', [JobController::class, 'updateJobStatusOnly']);
    })->add(AuthMiddleware::class);

    $app->group('/expenses', function (RouteCollectorProxy $expenses) {
        $expenses->get('', [ExpenseController::class, 'index']);
        $expenses->get('/load', [ExpenseController::class, 'load']);
        $expenses->get('/load/details', [ExpenseController::class, 'loadDetails']);
        $expenses->post('', [ExpenseController::class, 'store']);
        $expenses->get('/list', [ExpenseController::class, 'expenseList']);
        $expenses->delete('/{id:[0-9]+}', [ExpenseController::class, 'delete']);
        $expenses->get('/{id:[0-9]+}', [ExpenseController::class, 'get']);
        $expenses->post('/{id:[0-9]+}', [ExpenseController::class, 'update']);
    })->add(AuthMiddleware::class);

    $app->group('/payments', function (RouteCollectorProxy $payments) {
        
        $payments->group('/paydetails', function (RouteCollectorProxy $paydetails) {
            $paydetails->get('', [PaymentController::class, 'index']);
            $paydetails->get('/load', [PaymentController::class, 'load']);
            $paydetails->get('/load/details', [PaymentController::class, 'loadDetails']);
            $paydetails->post('', [PaymentController::class, 'store']);
            $paydetails->get('/list', [PaymentController::class, 'paymentList']);
            $paydetails->delete('/{id:[0-9]+}', [PaymentController::class, 'delete']);
            $paydetails->get('/{id:[0-9]+}', [PaymentController::class, 'get']);
            $paydetails->post('/{id:[0-9]+}', [PaymentController::class, 'update']);
        });

        $payments->group('/paystatus', function (RouteCollectorProxy $paystatus) {
            $paystatus->get('', [PayStatusController::class, 'index']);
            $paystatus->get('/load', [PayStatusController::class, 'load']);
            $paystatus->get('/load/details', [PayStatusController::class, 'loadDetails']);
            $paystatus->get('/load/paystatus', [PayStatusController::class, 'loadJobPaystatus']);
            $paystatus->post('', [PayStatusController::class, 'store']);
            $paystatus->get('/list', [PayStatusController::class, 'paystatusList']);
            $paystatus->delete('/{id:[0-9]+}', [PayStatusController::class, 'delete']);
            $paystatus->get('/{id:[0-9]+}', [PayStatusController::class, 'get']);
            $paystatus->post('/{id:[0-9]+}', [PayStatusController::class, 'update']);
        });
    })->add(AuthMiddleware::class);

    $app->group('/settings', function (RouteCollectorProxy $settings) {
        $settings->get('', [SettingsController::class, 'index']);
        $settings->group('/paymethod', function(RouteCollectorProxy $payMethod){
            $payMethod->get('', [PayMethodController::class, 'index']);
            $payMethod->get('/load', [PayMethodController::class, 'load']);
            $payMethod->post('', [PayMethodController::class, 'store']);
            $payMethod->get('/list', [PayMethodController::class, 'payMethodList']);
            $payMethod->delete('/{id:[0-9]+}', [PayMethodController::class, 'delete']);
            $payMethod->get('/{id:[0-9]+}', [PayMethodController::class, 'get']);
            $payMethod->post('/{id:[0-9]+}', [PayMethodController::class, 'update']);
        });
        $settings->group('/jobtype', function (RouteCollectorProxy $jobType) {
            $jobType->get('', [JobTypeController::class, 'index']);
            $jobType->get('/load', [JobTypeController::class, 'load']);
            $jobType->post('', [JobTypeController::class, 'store']);
            $jobType->delete('/{id:[0-9]+}', [JobTypeController::class, 'delete']);
            $jobType->get('/{id:[0-9]+}', [JobTypeController::class, 'get']);
            $jobType->post('/{id:[0-9]+}', [JobTypeController::class, 'update']);
        });
        $settings->group('/department', function (RouteCollectorProxy $department) {
            $department->get('', [DepartmentController::class, 'index']);
            $department->get('/load', [DepartmentController::class, 'load']);
            $department->post('', [DepartmentController::class, 'store']);
            $department->get('/list', [DepartmentController::class, 'departmentList']);
            $department->delete('/{id:[0-9]+}', [DepartmentController::class, 'delete']);
            $department->get('/{id:[0-9]+}', [DepartmentController::class, 'get']);
            $department->post('/{id:[0-9]+}', [DepartmentController::class, 'update']);
        });
        $settings->group('/users', function (RouteCollectorProxy $user) {
            $user->get('', [UserController::class, 'index']);
            $user->get('/load', [UserController::class, 'load']);
            $user->post('', [UserController::class, 'setRole']);
            $user->get('/list', [UserController::class, 'departmentList']);
            $user->delete('/{id:[0-9]+}', [UserController::class, 'delete']);
            $user->get('/{id:[0-9]+}', [UserController::class, 'get']);
            $user->post('/{id:[0-9]+}', [UserController::class, 'update']);
        });
    })->add(AuthMiddleware::class);

    $app->group('/category', function (RouteCollectorProxy $category) {
        $category->get('', [CategoryController::class, 'index']);
        $category->get('/load', [CategoryController::class, 'load']);
        $category->post('', [CategoryController::class, 'store']);
        $category->get('/list', [CategoryController::class, 'categoryList']);
        $category->delete('/{id:[0-9]+}', [CategoryController::class, 'delete']);
        $category->get('/{id:[0-9]+}', [CategoryController::class, 'get']);
        $category->post('/{id:[0-9]+}', [CategoryController::class, 'update']);
    })->add(AuthMiddleware::class);
    
    $app->group('/sponsor', function (RouteCollectorProxy $sponsor) {
        $sponsor->get('', [SponsorController::class, 'index']);
        $sponsor->get('/load', [SponsorController::class, 'load']);
        $sponsor->post('', [SponsorController::class, 'store']);
        $sponsor->get('/list', [SponsorController::class, 'sponsorList']);
        $sponsor->delete('/{id:[0-9]+}', [SponsorController::class, 'delete']);
        $sponsor->get('/{id:[0-9]+}', [SponsorController::class, 'get']);
        $sponsor->post('/{id:[0-9]+}', [SponsorController::class, 'update']);
    })->add(AuthMiddleware::class);
};