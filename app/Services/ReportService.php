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

    public function getJobTypesByIntervals(array $queryParams): array
    {
        $query = $this->entityManager
                    ->createQuery("SELECT SUM(j.amountDue) as totalBill, SUM(CAST(p.totalPaid AS float)) as totalPaid, jt.name as jobType, COUNT(jt.name) as count FROM App\Entity\Job j LEFT JOIN j.jobType jt LEFT JOIN j.paystatus p WHERE j.createdAt BETWEEN :param1 AND :param2 GROUP BY jobType ORDER BY jobType")
                    ->setParameters(
                                 [
                                     'param1' => $queryParams['from'] . ' 00:00:00',
                                     'param2' => $queryParams['to'] . ' 23:59:59'
                                 ]
                             )
                    ->getArrayResult();
        
       return $query;
    }

    public function getJobTotals(array $queryParams)
    {
        $query = $this->entityManager
                    ->createQuery("SELECT SUM(j.amountDue) as totalBill, SUM(CAST(p.totalPaid AS float)) as totalPaid FROM App\Entity\Job j LEFT JOIN j.paystatus p WHERE j.createdAt BETWEEN :param1 AND :param2 ")
                    ->setParameters(
                                 [
                                     'param1' => $queryParams['from'] . ' 00:00:00',
                                     'param2' => $queryParams['to'] . ' 23:59:59'
                                 ]
                             )
                    ->getSingleResult();
        
       return $query;
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

    public function expenseReportsByMonth(array $queryParams): array
    {
        $query = $this->entityManager
            ->createQuery("SELECT SUM(e.amount) as totalAmount, c.name as category, COUNT(c.name) as count FROM App\Entity\Expense e LEFT JOIN e.category c WHERE e.createdAt BETWEEN :param1 AND :param2 GROUP BY category ORDER BY category")
        ->setParameters(
                     [
                         'param1' => $queryParams['from'] . ' 00:00:00',
                         'param2' => $queryParams['to'] . ' 23:59:59'
                     ]
                 )
        ->getArrayResult();

        return $query;
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

    public function getExpenseTotals(array $queryParams)
    {
        $query = $this->entityManager
                    ->createQuery("SELECT SUM(e.amount) as totalExpense FROM App\Entity\Expense e WHERE e.createdAt BETWEEN :param1 AND :param2 ")
                    ->setParameters(
                                 [
                                     'param1' => $queryParams['from'] . ' 00:00:00',
                                     'param2' => $queryParams['to'] . ' 23:59:59'
                                 ]
                             )
                    ->getSingleResult();
        
       return $query;

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

    public function getMonthlyJobsSummary(array $queryParams)
    {
        $query = $this->entityManager
                    ->createQuery("SELECT SUM(j.amountDue) as bill, SUM(CAST(p.totalPaid AS float)) as paid, MONTH(j.createdAt) as month, MONTHNAME(j.createdAt) as month_name FROM App\Entity\Job j LEFT JOIN j.paystatus p WHERE YEAR(j.createdAt) = :year GROUP BY month_name, month ORDER BY month, month_name")
                    ->setParameter('year', $queryParams['year'])
                    ->getArrayResult();
        
       return $query;

    }

    public function getJobsByMonth(array $queryParams)
    {
        $query = $this->entityManager
                    ->createQuery("SELECT j.amountDue as bill, CAST(p.totalPaid AS float) as paid, DATE_FORMAT(j.createdAt, '%D') as date, jt.name as jobType, c.name as client, j.jobStatus FROM App\Entity\Job j LEFT JOIN j.jobType jt LEFT JOIN j.paystatus p LEFT JOIN j.client c WHERE MONTHNAME(j.createdAt) = :month_name AND YEAR(j.createdAt) = :year ORDER BY date DESC")
                    ->setParameter('month_name', $queryParams['month'])
                    ->setParameter('year', $queryParams['year'])
                    ->getArrayResult();
        
       return $query;

    }

    public function getMonthlyExpensesSummary(array $queryParams)
    {
        $query = $this->entityManager
                    ->createQuery("SELECT SUM(e.amount) as amount,  MONTH(e.date) as month, MONTHNAME(e.date) as month_name FROM App\Entity\Expense e WHERE YEAR(e.date) = :year GROUP BY month_name, month ORDER BY month, month_name")
                    ->setParameter('year', $queryParams['year'])
                    ->getArrayResult();
        
       return $query;

    }

    public function getExpensesByMonth(array $queryParams)
    {
        $query = $this->entityManager
                    ->createQuery("SELECT e.amount as amount, DATE_FORMAT(e.date, '%D') as date, c.name as category, s.name as sponsor, c.description as description FROM App\Entity\Expense e LEFT JOIN e.category c LEFT JOIN e.sponsor s WHERE MONTHNAME(e.date) = :month_name AND YEAR(e.date) = :year ORDER BY date DESC")
                    ->setParameter('month_name', $queryParams['month'])
                    ->setParameter('year', $queryParams['year'])
                    ->getArrayResult();
        
       return $query;

    }
}
