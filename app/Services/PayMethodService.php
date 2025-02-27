<?php

declare(strict_types = 1);

namespace App\Services;


use App\DataObjects\DataTableQueryParams;
use App\Entity\Paymethod;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PayMethodService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(array $data): Paymethod
    {
        $payMethod = new Paymethod();

        return $this->update($payMethod, $data);
    }

    public function getPaginatedPayMethods(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(PayMethod::class)
            ->createQueryBuilder('pm')
            ->leftJoin('pm.payments', 'p')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['name', 'createdAt']) ? $params->orderBy : 'createdAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('pm.name LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('pm.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $payMethod = $this->entityManager->find(PayMethod::class, $id);

        $this->entityManager->remove($payMethod);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?PayMethod
    {
        return $this->entityManager->find(PayMethod::class, $id);
    }

    public function update(PayMethod $payMethod, array $data): PayMethod
    {
        $payMethod->setName($data['name']);
        $payMethod->setDescription($data['description']);

        $this->entityManager->persist($payMethod);
        $this->entityManager->flush();

        return $payMethod;
    }

    public function getPayMethods(): array
    {
        return $this->entityManager
            ->getRepository(PayMethod::class)
            ->createQueryBuilder('pm')
            ->select('pm.id', 'pm.name')
            ->orderBy('pm.' . 'name', 'asc')
            ->getQuery()
            ->getArrayResult();
    }
}