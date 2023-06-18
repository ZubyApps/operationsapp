<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Expense;
use App\Entity\Job;
use App\Entity\JobType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ReportService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function jobReportsByMonth(array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->select('j', 'jt')
            ->leftJoin('j.jobType', 'jt');


        $query->where('j.createdAt >= :param1 and j.createdAt <= :param2')
            ->setParameters(
                [
                    'param1' => $queryParams['from'] . ' 00:00:00',
                    'param2' => $queryParams['to'] . ' 23:59:59'
                ]
            );

        $query->orderBy('jt.' . 'name', 'asc');

        return new Paginator($query);
    }

    public function listJobsByDateAndType(array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->select('j', 'jt', 'c')
            ->leftJoin('j.jobType', 'jt')
            ->leftJoin('j.client', 'c');


        $query->where('j.createdAt >= :param1 and j.createdAt <= :param2')
            ->setParameters(
                [
                    'param1' => $queryParams['from'] . ' 00:00:00',
                    'param2' => $queryParams['to'] . ' 23:59:59',
                ]
            );
        $query->andWhere('jt.name = :jobType')->setParameter('jobType', $queryParams['jobType']);

        $query->orderBy('j.' . 'createdAt', 'asc');

        return new Paginator($query);
    }

    public function expenseReportsByMonth(array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Expense::class)
            ->createQueryBuilder('e')
            ->select('e', 'ec')
            ->leftJoin('e.category', 'ec');


        $query->where('e.createdAt >= :param1 and e.createdAt <= :param2')
        ->setParameters(
            [
                'param1' => $queryParams['from'] . ' 00:00:00',
                'param2' => $queryParams['to'] . ' 23:59:59'
            ]
        );

        $query->orderBy('ec.' . 'name', 'asc');

        return new Paginator($query);
    }

    public function listExpensesByDateAndCategory(array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Expense::class)
            ->createQueryBuilder('e')
            ->select('e', 'ec', 's')
            ->leftJoin('e.category', 'ec')
            ->leftJoin('e.sponsor', 's');


        $query->where('e.createdAt >= :param1 and e.createdAt <= :param2')
        ->setParameters(
            [
                'param1' => $queryParams['from'] . ' 00:00:00',
                'param2' => $queryParams['to'] . ' 23:59:59',
            ]
        );
        $query->andWhere('ec.name = :category')->setParameter('category', $queryParams['category']);

        $query->orderBy('e.' . 'createdAt', 'asc');

        return new Paginator($query);
    }

    public function prepareTotalsArray(array $preparedTotalsArray, int $flag): array
    {
        return array_reduce(
            $preparedTotalsArray,
            function ($carry, $job) use ($flag) {

                foreach ($job as $type => $score) {

                    if (array_key_exists($type, $carry)) {
                        $carry[$type] += $flag ? $score[1] : $score[2];
                    } else {
                        $carry[$type] = $flag ? $score[1] : $score[2];
                    }
                }
                return  $carry;
            },
            []
        );
    }
}
