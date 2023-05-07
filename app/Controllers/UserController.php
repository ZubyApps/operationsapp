<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\UpdateUserData;
use App\Entity\User;
use App\Enum\UserRole;
use App\RequestValidators\UpdateUserRequestValidator;
use App\RequestValidators\UserRoleRequestValidator;
use App\ResponseFormatter;
use App\Services\DepartmentService;
use App\Services\RequestService;
use App\Services\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class UserController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly ResponseFormatter $responseFormatter,
        private readonly DepartmentService $departmentService,
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response, 
            'users/index.twig', 
            [
                'departments'   => $this->departmentService->getDepartments(),
                'users'         => $this->userService->getAll()
                ]
        );
    }

    public function setRole(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(UserRoleRequestValidator::class)->validate(
            $request->getParsedBody()
        );
        
        $this->userService->setRole($data['user'], UserRole::from($data['userRole']));
        

        return $response->withHeader('Location', '/settings/users')->withStatus(302);
    }


    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->userService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $user = $this->userService->getById((int) $args['id']);

        if (! $user) {
            return $response->withStatus(404);
        }


        $data = [
            'id'               => $user->getId(), 
            'firstname'        => $user->getFirstname(),
            'lastname'         => $user->getLastname(),
            'email'            => $user->getEmail(),
            'phonenumber'      => $user->getPhoneNumber(),
            'department'       => $user->getDepartment()->getId(),
            
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(UpdateUserRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $user = $this->userService->getById((int) $args['id']);

        if (! $user) {
            return $response->withStatus(404);
        }
        
        $this->userService->update(
            new UpdateUserData(
                $data['firstname'],
                $data['lastname'],
                $data['email'],
                $data['phonenumber'],
                $data['department'],
                $data['password']),
            $user);

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params      = $this->requestService->getDataTableQueryParameters($request);
        $users  = $this->userService->getPaginatedClients($params);
        $transformer = function (User $user) {
            return [
                'id'            => $user->getId(),
                'firstname'     => $user->getFirstname(),
                'phonenumber'   => $user->getPhoneNumber(),
                'department'    => $user->getDepartment()->getName(),
                'jobCount'      => $user->getJobs()->count(),
                'payCount'      => $user->getPayments()->count(),
                'role'          => $user->getUserRole(),
                'head'          => $user->getDepartment()->getHead()->getFirstname(),
                'createdAt'     => $user->getCreatedAt()->format('d/m/Y g:i A'),
                'activeUser'    => $this->userService->getActiveUserRole()
            ];
        };

        $totalUsers = count($users);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $users->getIterator()),
            $params->draw,
            $totalUsers
        );
    }

    public function UserList(Request $request, Response $response): Response
    {
        $data = $this->userService->getAll();

        return $this->responseFormatter->asJson($response,  $data);
    }
}
