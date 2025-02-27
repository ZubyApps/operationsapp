<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entity\Sponsor;
use App\RequestValidators\SponsorRequestValidator;
use App\ResponseFormatter;
use App\Services\RequestService;
use App\Services\SponsorService;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class SponsorController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly SponsorService $sponsorService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly UserService $userService
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'expense/sponsor/index.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(SponsorRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->sponsorService->create($data, $request->getAttribute('user'));

        return $response->withHeader('Location', '/sponsor')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->sponsorService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $sponsor = $this->sponsorService->getById((int) $args['id']);

        if (! $sponsor) {
            return $response->withStatus(404);
        }

        $data = [
            'id'            => $sponsor->getId(), 
            'name'          => $sponsor->getName(),
            'description'   => $sponsor->getDescription(),
            'flag'          => $sponsor->getFlag()
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(SponsorRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $sponsor = $this->sponsorService->getById((int) $data['id']);

        if (! $sponsor) {
            return $response->withStatus(404);
        }

        $this->sponsorService->update($sponsor, $data, $request->getAttribute('user'));

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params      = $this->requestService->getDataTableQueryParameters($request);
        $sponsors  = $this->sponsorService->getPaginatedSponsors($params);
        $transformer = function (Sponsor $sponsor) {
            return [
                'id'            => $sponsor->getId(),
                'name'          => $sponsor->getName(),
                'description'   => $sponsor->getDescription(),
                'flag'          => $sponsor->getFlag()->name,
                'count'         => $sponsor->getExpenses()->count(),
                'total'         => $sponsor->getExpenseTotal(),
                'activeUser'    => $this->userService->getActiveUserRole(),
                'createdAt'     => $sponsor->getCreatedAt()->format('d/m/Y g:i A'),
            ];
        };

        $totalsponsors = count($sponsors);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $sponsors->getIterator()),
            $params->draw,
            $totalsponsors
        );
    }

    public function sponsorList(Request $request, Response $response): Response
    {
        $data = $this->sponsorService->getSponsors();

        return $this->responseFormatter->asJson($response,  $data);
    }

}
