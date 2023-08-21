<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\RequestValidatorFactoryInterface;
use App\DataObjects\JobData;
use App\Entity\Job;;
use App\Enum\JobStatus;
use App\RequestValidators\CreateJobRequestValidator;
use App\RequestValidators\UpdateJobRequestValidator;
use App\ResponseFormatter;
use App\Services\JobService;
use App\Services\JobTypeService;
use App\Services\PayStatusService;
use App\Services\RequestService;
use App\Services\UserService;
use DateTime;
use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class JobController
{
    public function __construct(
        private readonly Twig $twig,
        private readonly RequestValidatorFactoryInterface $requestValidatorFactory,
        private readonly ResponseFormatter $responseFormatter,
        private readonly RequestService $requestService,
        private readonly JobService $jobService,
        private readonly JobTypeService $jobTypeService,
        private readonly UserService $userService,
        private readonly PayStatusService $payStatusService
        
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render(
            $response, 
            'jobs/index.twig',
            [
                'jobTypes' => $this->jobTypeService->getJobTypes()
                ]
        );
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $this->requestValidatorFactory->make(CreateJobRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        $job = $this->jobService->create(
            new JobData(
                $data['client'],
                $data['jobType'],
                $data['details'],
                $data['dueDate'] !== '' ? new DateTime($data['dueDate']) : null,
                (float) $data['bill'],
                JobStatus::from($data['jobStatus'])),
            $request->getAttribute('user'));
        
        $this->payStatusService->create($job, $request->getAttribute('user'));

        return $response->withHeader('Location', '/jobs')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->jobService->delete((int) $args['id']);

        return $response;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $job = $this->jobService->getById((int) $args['id']);

        if (! $job) {
            return $response->withStatus(404);
        }

        $data = [
            'id'        => $job->getId(),
            'clientId'  => $job->getClient()->getId(), 
            'client'    => $job->getClient()->getName(),
            'number'    => $job->getClient()->getPhoneNumber(),
            'jobType'   => $job->getJobtype()->getId(),
            'details'   => $job->getDetails(),
            'dueDate'   => $job->getDueDate() ? $job->getDueDate()->format('Y-m-d\TH:i') : 'N/A',
            'bill'      => $job->getAmountDue(),
            'jobStatus' => $job->getJobStatus(),
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = $this->requestValidatorFactory->make(UpdateJobRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $job = $this->jobService->getById((int) $data['id']);

        if (! $job) {
            return $response->withStatus(404);
        }

        $updatedJob = $this->jobService->update(
            $job,
            new jobData(
                $data['client'],
                $data['jobType'],
                $data['details'],
                $data['dueDate'] !== '' ? new DateTime($data['dueDate']) : null,
                (float) $data['bill'],
                JobStatus::from($data['jobStatus'])),
            $request->getAttribute('user'));

        $this->payStatusService->populate($updatedJob, $request->getAttribute('user'));

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $jobs           = $this->jobService->getPaginatedJobs($params);
        $transformer    = function (Job $job) {

            return [
                'id'            => $job->getId(),
                'date'          => $job->getCreatedAt()->format('d-M-Y'),
                'client'        => $job->getClient()->getName(),
                'number'        => $job->getClient()->getPhoneNumber(),
                'dueDate'       => $job->getDueDate() ? $job->getDueDate()->format('D d-M-y g:ia') : 'N/A',
                'jobType'       => $job->getJobtype()->getName(),
                'jobDetails'    => $job->getDetails(),
                'bill'          => $job?->getAmountDue(),
                'jobStatus'     => $job->getJobStatus(),
                'count'         => $job->getPayments()->count(),
                'activeUser'    => $this->userService->getActiveUserRole(),
            ];
        };

        $totalJobs = count($jobs);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $jobs->getIterator()),
            $params->draw,
            $totalJobs
        );
    }

    public function jobList(Request $request, Response $response): Response
    {
        $data = $this->jobService->getAll();

        return $this->responseFormatter->asJson($response,  $data);
    }

    public function getJobDetails(Request $request, Response $response, array $args): Response
    {
        $job = $this->jobService->getById((int) $args['id']);

        if (!$job) {
            return $response->withStatus(404);
        }

        $data = [
            'id'            => $job->getId(),
            'client'        => $job->getClient()->getName(),
            'number'        => $job->getClient()->getPhoneNumber(),
            'city'          => $job->getClient()->getCity(),
            'jobType'       => $job->getJobtype()->getName(),
            'dueDate'       => $job->getDueDate() ? $job->getDueDate()->format('D jS M y-g:i a') : 'N/A',
            'jobStatus'     => $job->getJobStatus(),
            'details'       => $job->getDetails(),
            'staff'         => $job->getUser()->getFirstname(),
            'createdAt'     => $job->getCreatedAt()->format('d-M-y g:ia'), 
            'updatedAt'     => $job->getUpdatedAt()->format('d-M-y g:ia')
        ];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function loadJobDetails(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $jobs           = $this->jobService->getPaginatedJobDetails($params, $request->getQueryParams());
        $transformer    = function (Job $job) {
            return [
                'id'                => $job->getId(),
                'client'            => $job->getClient()->getName(),
                'clientNumber'      => $job->getClient()->getPhoneNumber(),
                'dueDate'           => $job->getDueDate() ? $job->getDueDate()->format('D jS M y-g:i a') : 'N/A',
                'jobType'           => $job->getJobType()->getName(),
                'bill'              => $job->getAmountDue(),
                'jobStatus'         => $job->getJobStatus(),
                'staff'             => $job->getUser()->getFirstname(),
                'activeUser'        => $this->userService->getActiveUserRole(),
            ];
        };

        $totaljobs = count($jobs);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $jobs->getIterator()),
            $params->draw,
            $totaljobs
        );
    }

    public function loadBookedJobs(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $jobs           = $this->jobService->getPaginatedBookedJobs($params, $request->getQueryParams());
        $transformer    = function (Job $job) {
            return [
                'id'                => $job->getId(),
                'client'            => $job->getClient()->getName(),
                'jobType'           => $job->getJobType()->getName(),
                'days'              => $job->getDueDateDiff() ? $job->getDueDateDiff()->format('%r%a'): 'N/A',
                'hours'             => $job->getDueDateDiff() ? $job->getDueDateDiff()->h : 'N/A',
                'mins'              => $job->getDueDateDiff() ? $job->getDueDateDiff()->i : 'N/A',
                'activeUser'        => $this->userService->getActiveUserRole(),
            ];
        };

        $totaljobs = count($jobs);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $jobs->getIterator()),
            $params->draw,
            $totaljobs
        );
    }

    public function loadJobsInProgress(Request $request, Response $response): Response
    {
        $params         = $this->requestService->getDataTableQueryParameters($request);
        $jobs           = $this->jobService->getPaginatedJobsInProgress($params, $request->getQueryParams());
        $transformer    = function (Job $job) {
            return [
                'id'                => $job->getId(),
                'client'            => $job->getClient()->getName(),
                'jobType'           => $job->getJobType()->getName(),
                'details'           => $job->getDetails(),
                'days'              => $job->getDueDate() ? (new DateTimeImmutable())->diff($job->getDueDate())->days : (new DateTimeImmutable())->diff($job->getCreatedAt())->days,
                'activeUser'        => $this->userService->getActiveUserRole(),
            ];
        };

        $totaljobs = count($jobs);

        return $this->responseFormatter->asDataTable(
            $response,
            array_map($transformer, (array) $jobs->getIterator()),
            $params->draw,
            $totaljobs
        );
    }

    public function updateJobStatusOnly(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        $job = $this->jobService->getById((int) $args['id']);

        if (!$job) {
            return $response->withStatus(404);
        }

        $this->jobService->updateJobStatus($job, JobStatus::from($data['jobStatus']), $request->getAttribute('user'));

        return $response;
    }
}
