<?php

declare(strict_types = 1);

namespace App\Services;

use App\DataObjects\DataTableQueryParams;
use App\DataObjects\PaymentData;
use App\Entity\Payment;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaymentService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(PaymentData $data, User $user): Payment
    {
        $payment = new Payment();

        return $this->update($payment, $data, $user);
    }

    public function getPaginatedPayments(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Payment::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.client', 'c')
            ->leftJoin('p.job', 'j')
            ->leftJoin('j.jobType', 'jj')
            ->leftJoin('p.payMethod', 'pp')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['createdAt', 'amountpaid']) ? $params->orderBy : 'date';
        $orderDir = strtolower($params->orderDir) === 'desc' ? 'asc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('c.name LIKE :param or jj.name LIKE :param or pp.name LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $query->orderBy('p.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id): void
    {
        $payment = $this->entityManager->find(Payment::class, $id);

        $this->entityManager->remove($payment);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Payment
    {
        return $this->entityManager->find(Payment::class, $id);
    }

    public function update(Payment $payment, PaymentData $data, User $user): Payment
    {
        $payment->setAmountPaid($data->paid);
        $payment->setDate($data->date);
        $payment->setJob($data->job);
        $payment->setPayMethod($data->paymethod);
        $payment->setClient($data->job->getClient());

        $payment->setUser($user);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $payment;
    }

    public function getAll(): array
    {
        return $this->entityManager
            ->getRepository(Payment::class)
            ->createQueryBuilder('p')
            ->select('p.id', 'p.amountPaid')
            ->orderBy('p.' . 'updateAt', 'asc')
            ->getQuery()
            ->getArrayResult();
    }

    public function getPaginatedPaymentDetails(DataTableQueryParams $params, array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Payment::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.job', 'j')
            ->leftJoin('p.client', 'c');

        if ($queryParams['modal'] === 'detailsClientModal') {
            $query->Where('c.id = :id')->setParameter('id', $queryParams['id']);
        } else {
            $query->Where('j.id = :id')->setParameter('id', $queryParams['id']);
        }

        
        $query->orderBy('p.' . 'date', 'desc');

        return new Paginator($query);
    }
}