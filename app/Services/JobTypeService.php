<?php

declare(strict_types = 1);

namespace App\Services;


use App\DataObjects\DataTableQueryParams;
use App\Entity\JobType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class JobTypeService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(array $data): JobType
    {
        $jobType = new JobType();

        return $this->update($jobType, $data);
    }

    public function getPaginatedJobTypes(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(JobType::class)
            ->createQueryBuilder('jt')
            ->leftJoin('jt.jobs', 'j')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['name', 'createdAt']) ? $params->orderBy : 'createdAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('jt.name LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('jt.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $jobType = $this->entityManager->find(JobType::class, $id);

        $this->entityManager->remove($jobType);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?JobType
    {
        return $this->entityManager->find(JobType::class, $id);
    }

    public function update(JobType $jobType, array $data): JobType
    {
        $jobType->setName($data['name']);
        $jobType->setDescription($data['description']);

        $this->entityManager->persist($jobType);
        $this->entityManager->flush();

        return $jobType;
    }

    public function getJobTypes(): array
    {
        return $this->entityManager
            ->getRepository(JobType::class)
            ->createQueryBuilder('jt')
            ->select('jt.id', 'jt.name')
            ->orderBy('jt.' . 'name', 'asc')
            ->getQuery()
            ->getArrayResult();
    }
}
