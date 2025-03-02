<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\TaskData;
use App\Entity\Paystatus;
use App\Entity\Task;
use App\Enum\TaskStatus;
use App\RequestValidators\TaskRequestValidator;
use App\ResponseFormatter;
use App\Services\JobService;
use App\Services\RequestService;
use App\Services\TaskService;
use App\Services\UserService;
use DateTime;
use DateTimeZone;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class TaskController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly UserService $userService,
        private readonly TaskService $taskService,
        private readonly JobService $jobService
    ) { 
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response, 
            'tasks/index.twig',
            [''
            ]
        );
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(TaskRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $job = $this->jobService->getById((int) $data['id']);
        $assignedTo = $this->userService->getById((int) $data['assignedTo']);

        $this->taskService->create($job,
        new TaskData(
            $data['taskComment'],
            $assignedTo,
            $data['deadline'] !== '' ? new DateTime($data['deadline']) : null,
        ), $request->getAttribute('user'));

        return $response;
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->taskService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $paystatus = $this->taskService->getByJobId((int) $args['id']);;

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
        $tasks    = $this->taskService->getPaginatedTasks($params, $request->getQueryParams(), $request->getAttribute('user'));
        $count      = 1;
        $transformer    = function (Task $task) use($count) {
            return [
                'id'            => $task->getId(),
                'client'        => $task->getJob()->getClient()->getName(),
                'details'       => $task->getJob()->getDetails(),
                'taskComment'   => $task->getTaskComment(),
                'deadline'      => $task->getDeadline() ? $task->getDeadline()->format('Y-m-d H:i:s') : '',
                'assignedTo'    => $task->getAssignedTo()->getFirstname(),
                'status'        => $task->getTaskStatus(),
                'activeUser'    => $this->userService->getActiveUserRole(),
                'createdAt'     => $task->getCreatedAt()->format('d-m-y g:ia')
            ];
        };

        $totalTasks = count($tasks);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $tasks->getIterator()),
            $params->draw,
            $totalTasks
        );
    }

    public function updateTaskStatusOnly(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        $task = $this->taskService->getById((int) $args['id']);

        if (!$task) {
            return $response->withStatus(404);
        }

        $this->taskService->updateTaskStatus($task, TaskStatus::from($data['taskStatus']), $request->getAttribute('user'));

        return $response;
    }

    public function paystatusList(Request $request, Response $response): Response
    {
        $data = $this->taskService->getAll();

        return $this->responseFormatter->asJson($response,  $data);
    }

    public function loadDetails(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $paystatuses    = $this->taskService->getPaginatedPayStatusDetails($params, $request->getQueryParams());
        $transformer    = function (Paystatus $paystatus) {
            return [
                'id'      => $paystatus->getId(),
                'bill'    => $paystatus->getJob()->getAmountDue(),
                'paid'    => $paystatus->getJob()->getPaymentsTotal(),
                'balance' => $paystatus->getJob()->getAmountDue() - $paystatus->getJob()->getPaymentsTotal(),
                'status'  => $paystatus->getJob()->getAmountDue() ? round((float)($paystatus->getJob()->getPaymentsTotal() / $paystatus->getJob()->getAmountDue()) * 100, 2) : 0,
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
        $paystatuses    = $this->taskService->getPaginatedIncompletePayments($params, $request->getQueryParams());
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
