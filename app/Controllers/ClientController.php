<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\ClientData;
use App\Entity\Client;
use App\RequestValidators\CreateClientRequestValidator;
use App\RequestValidators\UpdateClientRequestValidator;
use App\ResponseFormatter;
use App\Services\ClientService;
use App\Services\RequestService;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class ClientController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ClientService $clientService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly UserService $userService
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'clients/index.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CreateClientRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->clientService->create(
            new ClientData(
                $data['name'],
                $data['number'],
                $data['email'],
                $data['city'],
                $data['state'],
                $data['country']),
            $request->getAttribute('user'));

        return $response->withHeader('Location', '/clients')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->clientService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $client = $this->clientService->getById((int) $args['id']);

        if (! $client) {
            return $response->withStatus(404);
        }

        $data = [
            'id'        => $client->getId(), 
            'name'      => $client->getName(),
            'number'    => $client->getPhoneNumber(),
            'email'     => $client->getEmail(),
            'city'      => $client->getCity(),
            'state'     => $client->getState(),
            'country'   => $client->getCountry(),
            'country'   => $client->getCountry(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(UpdateClientRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $client = $this->clientService->getById((int) $data['id']);

        if (! $client) {
            return $response->withStatus(404);
        }

        $this->clientService->update($client,
            new ClientData(
                $data['name'],
                $data['number'],
                $data['email'],
                $data['city'],
                $data['state'],
                $data['country']),
            $request->getAttribute('user'));

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $clients        = $this->clientService->getPaginatedClients($params);
        $transformer    = function (Client $client) {
            return [
                'id'                => $client->getId(),
                'name'              => $client->getName(),
                'email'             => $client->getEmail(),
                'number'            => $client->getPhoneNumber(),
                'city'              => $client->getCity(),
                'activeUser'        => $this->userService->getActiveUserRole(),
                'count'             => $client->getJobs()->count(),
                'paid'             => $client->getTotalPayments(),
                'createdAt'         => $client->getCreatedAt()->format('d/M/Y g:i A')
            ];
        };

        $totalClients = count($clients);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $clients->getIterator()),
            $params->draw,
            $totalClients
        );
    }

    public function clientList(Request $request, Response $response): Response
    {
        $data = $this->clientService->getAll();

        return $this->responseFormatter->asJson($response,  $data);
    }

    public function getDetails(Request $request, Response $response, array $args): Response
    {
        $client = $this->clientService->getById((int) $args['id']);

        if (!$client) {
            return $response->withStatus(404);
        }

        $data = [
            'id'            => $client->getId(),
            'name'          => $client->getName(),
            'number'        => $client->getPhoneNumber(),
            'email'         => $client->getEmail(),
            'city'          => $client->getCity(),
            'state'         => $client->getState(),
            'country'       => $client->getCountry(),
            'jobtotal'      => $client->getJobs()->count(),
            'staff'         => $client->getUser()->getFirstname(),
            'createdAt'     => $client->getCreatedAt()->format('d/M/Y g:i A'),
            'updatedAt'     => $client->getUpdatedAt()->format('d/M/Y g:i A'),
            'allbills'      => $client->getTotalBills(),
            'allpayments'   => $client->getTotalPayments(),
            'balance'       => $client->getTotalBills() - $client->getTotalPayments(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }
}
