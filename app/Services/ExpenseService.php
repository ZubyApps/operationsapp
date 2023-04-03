<?php

declare(strict_types = 1);

namespace App\Services;

use App\DataObjects\DataTableQueryParams;
use App\DataObjects\ExpenseData;
use App\Entity\Expense;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ExpenseService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(ExpenseData $data, User $user): Expense
    {
        $expense = new Expense();

        return $this->update($expense, $data, $user);
    }

    public function getPaginatedExpenses(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Expense::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.sponsor', 's')
            ->leftJoin('e.category', 'c')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['date', 'amount']) ? $params->orderBy : 'date';
        $orderDir = strtolower($params->orderDir) === 'desc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('c.name LIKE :param or s.name LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('e.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $expense = $this->entityManager->find(Expense::class, $id);

        $this->entityManager->remove($expense);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Expense
    {
        return $this->entityManager->find(Expense::class, $id);
    }

    public function update(Expense $expense, ExpenseData $data, User $user): Expense
    {
        $expense->setCategory($data->category);
        $expense->setSponsor($data->sponsor);
        $expense->setDate($data->date);
        $expense->setDescription($data->description);
        $expense->setAmount($data->amount);

        $expense->setUser($user);

        $this->entityManager->persist($expense);
        $this->entityManager->flush();

        return $expense;
    }

    public function getPaginatedExpenseDetails(DataTableQueryParams $params, array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Expense::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.user', 'u');

        if ($queryParams['modal'] === 'detailsClientModal') {
            $query->Where('u.id = :id')->setParameter('id', $queryParams['id']);
        } else {
            $query->Where('e.id = :id')->setParameter('id', $queryParams['id']);
        }

        
        $query->orderBy('e.' . 'date', 'desc');

        return new Paginator($query);
    }
}
