<?php

declare(strict_types = 1);

namespace App\Services;

use App\DataObjects\DataTableQueryParams;
use App\Entity\Job;
use App\Entity\Paystatus;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PayStatusService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(Job $job, User $user): Paystatus
    {
        $paystatus = new Paystatus();

        return $this->update($paystatus, $job, $user);
    }

    public function getPaginatedPayStatus(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Paystatus::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.job', 'j')
            ->leftJoin('j.jobType', 'jj')
            ->leftJoin('j.client', 'c')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['updatedAt', 'client']) ? $params->orderBy : 'updatedAt';
        $orderDir = strtolower($params->orderDir) === 'desc' ? 'desc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('jj.name LIKE :param or c.name LIKE :param or c.phoneNumber LIKE :param or j.jobStatus LIKE :param or j.createdAt LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('p.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $paystatus = $this->entityManager->find(Paystatus::class, $id);

        $this->entityManager->remove($paystatus);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Paystatus
    {
        return $this->entityManager->find(Paystatus::class, $id);
    }

    public function getByJobId(int $id)
    {
        return $this->entityManager->getRepository(Paystatus::class)->findOneBy(['job' => $id]);
    }

    public function populate(Job $job, User $user)
    {
        $paystatus = $this->getByJobId($job->getId());

        return $this->update($paystatus, $job, $user);
    }

    public function update(Paystatus $paystatus, Job $job, User $user): Paystatus
    {
        
        $paystatus->setJob($job);
        $paystatus->setTotalPaid($job->getPaymentsTotal());
        $paystatus->setUser($user);

        $this->entityManager->persist($paystatus);
        $this->entityManager->flush();

        return $paystatus;
    }

    public function getAll(): array
    {
        return $this->entityManager
            ->getRepository(Paystatus::class)
            ->createQueryBuilder('p')
            ->select('p.id', 'p.amountPaid')
            ->orderBy('p.' . 'updateAt', 'asc')
            ->getQuery()
            ->getArrayResult();
    }

    public function getPaginatedPayStatusDetails(DataTableQueryParams $params, array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Paystatus::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.job', 'j')
            ->leftJoin('j.client', 'c');

        if ($queryParams['modal'] === 'detailsClientModal') {
            $query->Where('c.id = :id')->setParameter('id', $queryParams['id']);
        } else {
            $query->Where('j.id = :id')->setParameter('id', $queryParams['id']);
        }

        
        $query->orderBy('p.' . 'id', 'asc');

        return new Paginator($query);
    }

    public function getPaginatedIncompletePayments(DataTableQueryParams $params, array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Paystatus::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.job', 'j');

        $query->where('p.totalPaid < j.amountDue');

        $query->orderBy('p.' . 'id', 'desc');

        return new Paginator($query);
    }
}
