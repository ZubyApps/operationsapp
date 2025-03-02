<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\HasTimestamps;
use App\Enum\TaskStatus;
use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(), Table('tasks')]
#[HasLifecycleCallbacks]
class Task
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(nullable:true)]
    private string $taskComment;

    #[Column(nullable:true)]
    private string $pendingComment;

    #[Column(nullable:true)]
    private string $inprogressComment;

    #[Column(nullable:true)]
    private string $completedComment;
    
    #[Column(nullable: \true)]
    private DateTime $deadline;

    #[Column(nullable: \true)]
    private DateTime $inprogressDate;

    #[Column(nullable: \true)]
    private DateTime $completedDate;

    #[Column(nullable: \true)]
    private TaskStatus $taskStatus;

    #[ManyToOne(inversedBy: 'tasks')]
    private Job $job;

    #[ManyToOne(inversedBy: 'tasks')]
    private User $user;

    #[ManyToOne(inversedBy: 'tasks')]
    #[JoinColumn(onDelete: 'CASCADE')]
    private User $assignedTo;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTaskComment(): ?string
    {
        return $this->taskComment;
    }

    public function setTaskComment(string $taskComment): Task
    {
        $this->taskComment = $taskComment;

        return $this;
    }

    public function getPendingComment(): ?string
    {
        return $this->pendingComment;
    }

    public function setPendingComment(string $pendingComment): Task
    {
        $this->pendingComment = $pendingComment;

        return $this;
    }

    public function getInprogressComment(): ?string
    {
        return $this->inprogressComment;
    }

    public function setInprogressComment(string $inprogressComment): Task
    {
        $this->inprogressComment = $inprogressComment;

        return $this;
    }

    public function getCompletedComment(): ?string
    {
        return $this->inprogressComment;
    }

    public function setCompletedComment(string $completedComment): Task
    {
        $this->completedComment = $completedComment;

        return $this;
    }

    public function getDeadline(): DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(DateTime $deadline): Task
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getInprogressDate(): DateTime
    {
        return $this->inprogressDate;
    }

    public function setInprogressDate(DateTime $inProgressDate): Task
    {
        $this->inprogressDate = $inProgressDate;

        return $this;
    }

    public function getCompletedDate(): DateTime
    {
        return $this->completedDate;
    }

    public function setCompletedDate(DateTime $completedDate): Task
    {
        $this->completedDate = $completedDate;

        return $this;
    }

    public function getTaskStatus(): TaskStatus
    {
        return $this->taskStatus;
    }

    public function setTaskStatus(TaskStatus $taskStatus): Task
    {
        $this->taskStatus = $taskStatus;

        return $this;
    }

    public function getJob(): Job
    {
        return $this->job;
    }

    public function setJob(Job $job): Task
    {
        $job->addTasks($this);

        $this->job = $job;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Task
    {
        $user->addTask($this);

        $this->user = $user;

        return $this;
    }

    public function getAssignedTo(): User
    {
        if (! isset($this->assignedTo)){
            $this->assignedTo = new User();
        }
        return $this->assignedTo;
    }

    public function setAssignedTo(User $assignedTo): Task
    {
        $assignedTo->addAssignedTo($this);

        $this->assignedTo = $assignedTo;

        return $this;
    }
}