<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\DepartmentData;
use App\Entity\Department;
use App\RequestValidators\DepartmentRequestValidator;
use App\ResponseFormatter;
use App\Services\DepartmentService;
use App\Services\RequestService;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class DepartmentController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ResponseFormatter $responseFormatter,
        private readonly DepartmentService $departmentService,
        private readonly RequestService $requestService,
        private readonly UserService $userService
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response, 
            'users/department/index.twig', 
            ['users' => $this->userService->getAll() ]);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(DepartmentRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $this->departmentService->create(
            new DepartmentData(
                $data['name'],
                $data['description'],
                $data['head']
            ) 
        );

        return $response->withHeader('Location', '/settings/department')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->departmentService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $department = $this->departmentService->getById((int) $args['id']);

        if (! $department) {
            return $response->withStatus(404);
        }

        $data = [
            'id'            => $department->getId(), 
            'name'          => $department->getName(),
            'description'   => $department->getDescription(),
            'head'          => $department->getHead()->getId(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(DepartmentRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $department = $this->departmentService->getById((int) $data['id']);

        if (! $department) {
            return $response->withStatus(404);
        }

        $this->departmentService->update($department, new DepartmentData(
            $data['name'],
            $data['description'],
            $data['head']
        ));

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $departments    = $this->departmentService->getPaginatedDepartments($params);
        $transformer    = function (Department $department) {
            return [
                'id'            => $department->getId(),
                'name'          => $department->getName(),
                'description'   => $department->getDescription(),
                'head'          => $department->getHead()->getFirstname(),
                'count'         => $department->getUsers()->count(),
                'activeUser'    => $this->userService->getActiveUserRole(),
                'createdAt'     => $department->getCreatedAt()->format('d/m/Y g:i A'),
            ];
        };

        $totalDepartments = count($departments);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $departments->getIterator()),
            $params->draw,
            $totalDepartments
        );
    }

    public function departmentList(Request $request, Response $response): Response
    {
        $data = $this->departmentService->getdepartments();

        return $this->responseFormatter->asJson($response,  $data);
    }

}
