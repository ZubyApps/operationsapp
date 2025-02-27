<?php

declare(strict_types = 1);

namespace App\Services;


use App\DataObjects\DataTableQueryParams;
use App\DataObjects\DepartmentData;
use App\Entity\Department;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DepartmentService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(DepartmentData $data): Department
    {
        $department = new Department();

        return $this->update($department, $data);
    }

    public function getPaginatedDepartments(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Department::class)
            ->createQueryBuilder('d')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['name', 'head', 'createdAt']) ? $params->orderBy : 'createdAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('d.name LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('d.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $department = $this->entityManager->find(Department::class, $id);

        $this->entityManager->remove($department);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Department
    {
        return $this->entityManager->find(Department::class, $id);
    }

    public function update(Department $department, DepartmentData $data): Department
    {
        $department->setName($data->name);
        $department->setDescription($data->description);
        $department->setHead($data->head);

        $this->entityManager->persist($department);
        $this->entityManager->flush();

        return $department;
    }

    public function getDepartments(): array
    {
        return $this->entityManager
            ->getRepository(Department::class)
            ->createQueryBuilder('d')
            ->select('d.id', 'd.name')
            ->orderBy('d.' . 'name', 'asc')
            ->getQuery()
            ->getArrayResult();
    }
}