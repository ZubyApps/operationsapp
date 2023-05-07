<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entity\Job;
use App\Entity\Paystatus;
use App\Entity\User;
use App\ResponseFormatter;
use App\Services\ClientService;
use App\Services\JobTypeService;
use App\Services\PayMethodService;
use App\Services\PayStatusService;
use App\Services\RequestService;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class PayStatusController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly PayStatusService $payStatusService,
        private readonly PayMethodService $payMethodService,
        private readonly JobTypeService $jobTypeService,
        private readonly ClientService $clientService
    ) { 
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response, 
            'paystatus/index.twig',
            [
                'paymethods'    => $this->payMethodService->getPayMethods(),
                'jobTypes'      => $this->jobTypeService->getJobTypes(),
            ]
        );
    }

    public function store($data, $user): Paystatus
    {
        return $this->payStatusService->create($data, $user);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->payStatusService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $paystatus = $this->payStatusService->getByJobId((int) $args['id']);;

        if (! $paystatus) {
            return $response->withStatus(404);
        }

        $data = [
            'id'            => $paystatus->getId(),
            'client'        => $paystatus->getJob()->getClient()->getName(),
            'number'        => $paystatus->getJob()->getClient()->getPhoneNumber(),
            'jobId'         => $paystatus->getJob()->getId(),
            'details'       => $paystatus->getJob()->getDetails(),
            'dueDate'       => $paystatus->getJob()->getDueDate() ? $paystatus->getJob()->getDueDate()->format('Y-m-d\TH:i') : 'N/A',
            'jobType'       => $paystatus->getJob()->getJobType()->getId(),
            'bill'          => $paystatus->getJob()->getAmountDue(),
            'balance'       => $paystatus->getJob()->getAmountDue() - $paystatus->getJob()->getPaymentsTotal(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function load(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $paystatuses    = $this->payStatusService->getPaginatedPaystatus($params);
        $transformer    = function (Paystatus $paystatus) {
            return [
                'id'        => $paystatus->getId(),
                'jobId'     => $paystatus->getJob()->getId(),
                'client'    => $paystatus->getJob()->getClient()->getName(),
                'number'    => $paystatus->getJob()->getClient()->getPhoneNumber(),
                'jobtype'   => $paystatus->getJob()->getJobType()->getName(),
                'jobstatus' => $paystatus->getJob()->getJobStatus(),
                'bill'      => $paystatus->getJob()->getAmountDue(),
                'paid'      => $paystatus->getJob()->getPaymentsTotal(),
                'balance'   => $paystatus->getJob()->getAmountDue() - $paystatus->getJob()->getPaymentsTotal(),
                'status'    => round((float)($paystatus->getJob()->getPaymentsTotal()/$paystatus->getJob()->getAmountDue())*100, 2),
                'staff'     => $paystatus->getUser()->getFirstname(),
                'activeUser'=> $this->userService->getActiveUserRole(),
                'createdAt' => $paystatus->getCreatedAt()->format('d-M-Y g:ia')
            ];
        };

        $totalPaystatuses = count($paystatuses);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $paystatuses->getIterator()),
            $params->draw,
            $totalPaystatuses
        );
    }

    public function paystatusList(Request $request, Response $response): Response
    {
        $data = $this->payStatusService->getAll();

        return $this->responseFormatter->asJson($response,  $data);
    }

    public function loadDetails(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $paystatuses    = $this->payStatusService->getPaginatedPayStatusDetails($params, $request->getQueryParams());
        $transformer    = function (Paystatus $paystatus) {
            return [
                'id'      => $paystatus->getId(),
                'bill'    => $paystatus->getJob()->getAmountDue(),
                'paid'    => $paystatus->getJob()->getPaymentsTotal(),
                'balance' => $paystatus->getJob()->getAmountDue() - $paystatus->getJob()->getPaymentsTotal(),
                'status'  => round((float) ($paystatus->getJob()->getPaymentsTotal() / $paystatus->getJob()->getAmountDue()) * 100, 2),
            ];
        };

        $totalpaystatuses = count($paystatuses);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $paystatuses->getIterator()),
            $params->draw,
            $totalpaystatuses
        );
    }

    public function loadJobPaystatus(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $paystatuses    = $this->payStatusService->getPaginatedIncompletePayments($params, $request->getQueryParams());
        $transformer    = function (Paystatus $paystatus) {
            return [
                'id'      => $paystatus->getId(),
                'jobId'   => $paystatus->getJob()->getId(),
                'client'  => $paystatus->getJob()->getClient()->getName(),
                'job'     => $paystatus->getJob()->getJobStatus(),
                'bill'    => $paystatus->getJob()->getAmountDue(),
                'paid'    => $paystatus->getJob()->getPaymentsTotal(),
                'status'  => round((float) ($paystatus->getJob()->getPaymentsTotal() / $paystatus->getJob()->getAmountDue()) * 100, 2),
            ];
        };

        $totalpaystatuses = count($paystatuses);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $paystatuses->getIterator()),
            $params->draw,
            $totalpaystatuses
        );
    }
}
