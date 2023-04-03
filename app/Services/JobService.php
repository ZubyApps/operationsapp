<?php

declare(strict_types = 1);

namespace App\Services;


use App\DataObjects\DataTableQueryParams;
use App\DataObjects\JobData;
use App\Entity\Job;
use App\Entity\User;
use App\Enum\JobStatus;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class JobService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(JobData $data, User $user): Job
    {
        $job = new Job();

        return $this->update($job, $data, $user);
    }

    public function getPaginatedJobs(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->leftJoin('j.client', 'c')
            ->leftJoin('j.user', 'u')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['createdAt', 'jobType', 'dueDate', 'jobStatus', 'client', 'staff']) ? $params->orderBy : 'createdAt';
        $orderDir = strtolower($params->orderDir) === 'desc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('c.name LIKE :param or c.phoneNumber LIKE :param or j.jobStatus LIKE :param or j.dueDate LIKE :param or j.createdAt LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        if ($orderBy === 'client') {
            $query->orderBy('c.name', $orderDir);
        } elseif ($orderBy === 'staff') {
            $query->orderBy('u.firstname', $orderDir);
        }else {
            $query->orderBy('j.' . $orderBy, $orderDir);
        }

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $job = $this->entityManager->find(Job::class, $id);

        $this->entityManager->remove($job);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Job
    {
        return $this->entityManager->find(Job::class, $id);
    }

    public function update(Job $job, JobData $data, User $user): Job
    {
        $job->setClient($data->client);
        $job->setJobtype($data->type);
        $job->setDetails($data->details);
        $job->setDueDate($data->dueDate);
        $job->setAmountDue($data->bill);
        $job->setJobStatus($data->jobStatus);

        $job->setUser($user);

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        return $job;
    }

    public function getAll(): array
    {
        return $this->entityManager
            ->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->select('j.id', 'j.JobType', 'j.details', 'dueDate')
            ->orderBy('j.' . 'dueDate', 'asc')
            ->getQuery()
            ->getArrayResult();
    }

    public function getPaginatedJobDetails(DataTableQueryParams $params, array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->leftJoin('j.user', 'u')
            ->leftJoin('j.client', 'c');

        if ($queryParams['modal'] === 'detailsClientModal') {
            $query->Where('c.id = :id')->setParameter('id', $queryParams['id']);
        } else {
            $query->Where('j.id = :id')->setParameter('id', $queryParams['id']);
        }


        $query->orderBy('j.' . 'dueDate', 'desc');

        return new Paginator($query);
    }

    public function getPaginatedBookedJobs(DataTableQueryParams $params, array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->leftJoin('j.client', 'c');

        $query->Where('j.jobStatus = :status')->setParameter('status', 'Booked');
        $query->orderBy('j.dueDate', 'asc');

        return new Paginator($query);
    }

    public function getPaginatedJobsInProgress(DataTableQueryParams $params, array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Job::class)
            ->createQueryBuilder('j');

        $query->Where('j.jobStatus = :status')->setParameter('status', 'Inprogress');
        $query->orderBy('j.dueDate', 'asc');

        return new Paginator($query);
    }

    public function updateJobStatus(Job $job, JobStatus $data, User $user): Job
    {
        $job->setJobStatus($data);

        $job->setUser($user);

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        return $job;
    }
}