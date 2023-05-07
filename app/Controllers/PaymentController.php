<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\PaymentData;
use App\Entity\Payment;
use App\RequestValidators\PaymentRequestValidator;
use App\ResponseFormatter;
use App\Services\ClientService;
use App\Services\PaymentService;
use App\Services\PayMethodService;
use App\Services\PayStatusService;
use App\Services\RequestService;
use App\Services\UserService;
use DateTime;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class PaymentController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly PaymentService $paymentService,
        private readonly ClientService $clientService,
        private readonly UserService $userService,
        private readonly PayMethodService $payMethodService,
        private readonly PayStatusService $payStatusService
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response, 
            'payments/index.twig'
        );
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(PaymentRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->paymentService->create(
            new PaymentData(
                (float)$data['paid'],
                new DateTime($data['date']),
                $data['job'],
                $data['paymethod']),
            $request->getAttribute('user'));

        $this->payStatusService->populate($data['job'], $request->getAttribute('user'));

        return $response->withHeader('Location', '/payments/paydetails')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->paymentService->delete((int) $args['id']);

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $payments       = $this->paymentService->getPaginatedPayments($params);
        $transformer    = function (Payment $payment) {
            return [
                'id'                => $payment->getId(),
                'createdAt'         => $payment->getCreatedAt()->format('d-M-y g:ia'),
                'client'            => $payment->getClient()->getName(),
                'jobtype'           => $payment->getJob()->getJobType()->getName(),
                'paid'              => $payment->getAmountPaid(),
                'balance'           => $payment->getJob()->getAmountDue() - $payment->getJob()->getPaymentsTotal(),
                'paymethod'         => $payment->getPayMethod()->getName(),
                'date'              => $payment->getDate()->format('d-M-Y'),
                'staff'             => $payment->getUser()->getFirstname(),
                'activeUser'        => $this->userService->getActiveUserRole(),
            ];
        };

        $totalPayments = count($payments);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $payments->getIterator()),
            $params->draw,
            $totalPayments
        );
    }

    public function loadDetails(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $payments       = $this->paymentService->getPaginatedPaymentDetails($params, $request->getQueryParams());
        $transformer    = function (Payment $payment) {
            return [
                'id'                => $payment->getId(),
                'date'              => $payment->getDate()->format('d-M-Y'),
                'amount'            => $payment->getAmountPaid(),
                'paymethod'         => $payment->getPayMethod()->getName(),
                'staff'             => $payment->getUser()->getFirstname(),
                'activeUser'        => $this->userService->getActiveUserRole(),
            ];
        };

        $totalPayments = count($payments);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $payments->getIterator()),
            $params->draw,
            $totalPayments
        );
    }
}
