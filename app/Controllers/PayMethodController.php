<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entity\PayMethod;
use App\RequestValidators\PayMethodRequestValidator;
use App\ResponseFormatter;
use App\Services\PayMethodService;
use App\Services\RequestService;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class PayMethodController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly RequestService $requestService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly PayMethodService $payMethodService,
        private readonly UserService $userService,
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'payments/method/index.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(PayMethodRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->payMethodService->create($data);

        return $response->withHeader('Location', '/settings/paymethod')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->payMethodService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $payMethod = $this->payMethodService->getById((int) $args['id']);

        if (! $payMethod) {
            return $response->withStatus(404);
        }

        $data = [
            'id'            => $payMethod->getId(), 
            'name'          => $payMethod->getName(),
            'description'   => $payMethod->getDescription(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(PayMethodRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $payMethod = $this->payMethodService->getById((int) $data['id']);

        if (! $payMethod) {
            return $response->withStatus(404);
        }

        $this->payMethodService->update($payMethod, $data);

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params      = $this->requestService->getDataTableQueryParameters($request);
        $payMethods  = $this->payMethodService->getPaginatedPayMethods($params);
        $transformer = function (PayMethod $payMethod) {
            return [
                'id'            => $payMethod->getId(),
                'name'          => $payMethod->getName(),
                'description'   => $payMethod->getDescription(),
                'count'         => $payMethod->getPayments()->count(),
                'total'         => $payMethod->getPaymentsTotal(),
                'activeUser'    => $this->userService->getActiveUserRole(),
                'createdAt'     => $payMethod->getCreatedAt()->format('m/d/Y g:i A'),
            ];
        };

        $totalpayMethods = count($payMethods);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $payMethods->getIterator()),
            $params->draw,
            $totalpayMethods
        );
    }

    public function payMethodList(Request $request, Response $response): Response
    {
        $data = $this->payMethodService->getpayMethods();

        return $this->responseFormatter->asJson($response,  $data);
    }
}