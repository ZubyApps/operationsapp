<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\HasTimestamps;
use App\Enum\JobStatus;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(), Table('jobs')]
#[HasLifecycleCallbacks]
class Job
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[ManyToOne(inversedBy: 'jobs')]
    private JobType $jobType;

    #[Column(nullable: \true)]
    private string $details;

    #[Column(nullable: \true)]
    private DateTime|null $dueDate;

    #[Column(nullable: \true)]
    private float|null $amountDue;

    #[Column(nullable: \true)]
    private JobStatus|null $jobStatus;

    #[ManyToOne(inversedBy: 'jobs')]
    private User $user;

    #[ManyToOne(inversedBy: 'jobs')]
    private Client $client;

    #[OneToOne(targetEntity: Paystatus::class, mappedBy: 'job', cascade:['remove'])]
    private Paystatus $paystatus;

    #[OneToMany(mappedBy: 'job', targetEntity: Payment::class)]
    private Collection $payments;

    #[OneToMany(mappedBy: 'job', targetEntity: Task::class)]
    private Collection $tasks;

    public function __construct()
    {
        $this->payments  = new ArrayCollection();
        $this->tasks  = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getJobType(): JobType|null
    {
        return $this->jobType;
    }

    public function setJobType(JobType $jobType): Job
    {
        $jobType->addJob($this);

        $this->jobType = $jobType;

        return $this;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function setDetails(string $details): Job
    {
        $this->details = $details;

        return $this;
    }

    public function getDueDate(): ?DateTime
    {
        return $this->dueDate;
    }

    public function getDueDateDiff(): DateInterval
    {
        $dateTime = (new Datetime())->setTimezone(new DateTimeZone('Africa/Lagos'));
        $interval = new DateInterval('PT1H');
        
        return $this->dueDate ? $dateTime->add($interval)->diff($this->dueDate) : 'N/A';
        
    }

    public function setDueDate(?DateTime $dueDate): Job
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getAmountDue(): ?float
    {
        return $this->amountDue;
    }

    public function setAmountDue(?float $amountDue): Job
    {
        $this->amountDue = $amountDue;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Job
    {
        $user->addJob($this);

        $this->user = $user;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): Job
    {
        $client->addJob($this);

        $this->client = $client;

        return $this;
    }

    public function getPayments(): ArrayCollection|Collection
    {
        return $this->payments;
    }

    public function addPayments(Payment $payments): Job
    {
        $this->payments->add($payments);

        return $this;
    }

    public function getPaymentsTotal(): float
    {
        $total = 0;
        foreach ($this->payments as $payment) {
            $total += $payment->getAmountPaid();
        }

        return $total;
    }

    public function getJobStatus(): JobStatus
    {
        return $this->jobStatus;
    }

    public function setJobStatus(JobStatus $jobStatus): Job
    {
        $this->jobStatus = $jobStatus;

        return $this;
    }

    public function getPaystatus(): ?Paystatus
    {
        return $this->paystatus;
    }

    public function setPaystatus(?Paystatus $paystatus): Job
    {
        $this->paystatus = $paystatus;

        return $this;
    }

    public function getTasks(): ArrayCollection|Collection
    {
        return $this->tasks;
    }

    public function addTasks(Task $tasks): Job
    {
        $this->tasks->add($tasks);

        return $this;
    }
}