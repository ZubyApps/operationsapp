<?php

declare(strict_types = 1);

namespace App\Services;

use App\DataObjects\DataTableQueryParams;
use App\DataObjects\TaskData;
use App\Entity\Job;
use App\Entity\Paystatus;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TaskService
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    public function create(Job $job, TaskData $taskData, User $user): Task
    {
        $task = new Task();

        return $this->update($task, $taskData, $job, $user);
    }

    public function getPaginatedTasks(DataTableQueryParams $params, array $queryParams, User $user): Paginator
    {
        // var_dump($queryParams);
        $query = $this->entityManager
            ->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.job', 'j')
            ->leftJoin('j.client', 'c')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy  = in_array($params->orderBy, ['createdAt']) ? $params->orderBy : 'createdAt';
        $orderDir = strtolower($params->orderDir) === 'desc' ? 'desc' : 'desc';

        if (! empty($params->searchTerm)) {
            $query->where('jj.name LIKE :param or c.name LIKE :param or c.phoneNumber LIKE :param or j.jobStatus LIKE :param or j.createdAt LIKE :param')->setParameter('param', '%' . addcslashes($params->searchTerm, '%_') . '%');
        }

        $role = $user->getUserRole();
        // var_dump($role);

        if ($role !== UserRole::Admin && $role !== UserRole::Reception) {
            $query->Where('t.assignedTo = :id')->setParameter('id', $user->getId());
            if ($queryParams['filter'] == 'Unfinished'){
                $query->andWhere('t.taskStatus != :status')->setParameter('status', TaskStatus::from('Finished'));
            }
            if ($queryParams['filter'] == 'Finished'){
                $query->andWhere('t.taskStatus = :status')->setParameter('status', TaskStatus::from('Finished'));
            }
        } else {
            if ($queryParams['filter'] == 'Unfinished'){
                $query->andWhere('t.taskStatus != :status')->setParameter('status', TaskStatus::from('Finished'));
            }
            if ($queryParams['filter'] == 'Finished'){
                $query->andWhere('t.taskStatus = :status')->setParameter('status', TaskStatus::from('Finished'));
            }
        }
        
        $query->orderBy('t.' . $orderBy, $orderDir);
        return new Paginator($query);

    }

    public function delete(int $id): void
    {
        $task = $this->entityManager->find(Task::class, $id);

        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }

    public function getById(int $id): ?Task
    {
        return $this->entityManager->find(Task::class, $id);
    }

    public function getByJobId(int $id)
    {
        return $this->entityManager->getRepository(Task::class)->findOneBy(['job' => $id]);
    }

    public function populate(Job $job, $taskData, User $user)
    {
        $task = $this->getByJobId($job->getId());

        return $this->update($task, $taskData, $job, $user);
    }

    public function update(Task $task, TaskData $taskData, Job $job, User $user): Task
    {
        
        $task->setJob($job);
        $task->setAssignedTo($taskData->assignedTo);
        $task->setDeadline($taskData->deadline);
        $task->setTaskComment($taskData->taskComment);
        $task->setTaskStatus(TaskStatus::Pending);
        $task->setUser($user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    public function getAll(): array
    {
        return $this->entityManager
            ->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->select('t.id', 'p.taskComment')
            ->orderBy('t.' . 'updateAt', 'asc')
            ->getQuery()
            ->getArrayResult();
    }

    public function getPaginatedPayStatusDetails(DataTableQueryParams $params, array $queryParams): Paginator
    {
        $query = $this->entityManager
            ->getRepository(Task::class)
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
            ->getRepository(task::class)
            ->createQueryBuilder('t')
            ->leftJoin('t.job', 'j');

        $query->where('p.totalPaid < j.amountDue');

        $query->orderBy('p.' . 'id', 'desc');

        return new Paginator($query);
    }

    public function updateTaskStatus(Task $task, TaskStatus $data, User $user): Task
    {
        $task->setTaskStatus($data);

        // $task->setUser($user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }
}
