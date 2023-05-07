<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Entity\JobType;
use App\RequestValidators\JobTypeRequestValidator;
use App\ResponseFormatter;
use App\Services\JobTypeService;
use App\Services\RequestService;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class JobTypeController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly JobTypeService $jobTypeService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly UserService $userService,
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'jobs/type/index.twig');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(JobTypeRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->jobTypeService->create($data);

        return $response->withHeader('Location', '/settings/jobtype')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->jobTypeService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $jobType = $this->jobTypeService->getById((int) $args['id']);

        if (! $jobType) {
            return $response->withStatus(404);
        }

        $data = [
            'id'            => $jobType->getId(), 
            'name'          => $jobType->getName(),
            'description'   => $jobType->getDescription(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(JobTypeRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $jobType = $this->jobTypeService->getById((int) $data['id']);

        if (! $jobType) {
            return $response->withStatus(404);
        }

        $this->jobTypeService->update($jobType, $data);

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params      = $this->requestService->getDataTableQueryParameters($request);
        $jobTypes  = $this->jobTypeService->getPaginatedJobTypes($params);
        $transformer = function (JobType $jobType) {
            return [
                'id'            => $jobType->getId(),
                'name'          => $jobType->getName(),
                'description'   => $jobType->getDescription(),
                'count'         => $jobType->getJobs()->count(),
                'total'         => $jobType->getJobPaymentsTotal(),
                'activeUser'    => $this->userService->getActiveUserRole(),
                'createdAt'     => $jobType->getCreatedAt()->format('d/m/Y g:i A'),
            ];
        };

        $totalJobTypes = count($jobTypes);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $jobTypes->getIterator()),
            $params->draw,
            $totalJobTypes
        );
    }
}
