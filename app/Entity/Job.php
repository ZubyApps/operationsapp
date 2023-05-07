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

    public function __construct()
    {
        $this->payments  = new ArrayCollection();
    }

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of jobType
     */
    public function getJobType(): JobType|null
    {
        return $this->jobType;
    }

    /**
     * Set the value of jobType
     */
    public function setJobType(JobType $jobType): Job
    {
        $jobType->addJob($this);

        $this->jobType = $jobType;

        return $this;
    }

    /**
     * Get the value of details
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * Set the value of details
     */
    public function setDetails(string $details): Job
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get the value of dueDate
     */
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

    /**
     * Set the value of dueDate
     */
    public function setDueDate(?DateTime $dueDate): Job
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get the value of amountDue
     */
    public function getAmountDue(): ?float
    {
        return $this->amountDue;
    }

    /**
     * Set the value of amountDue
     */
    public function setAmountDue(?float $amountDue): Job
    {
        $this->amountDue = $amountDue;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set the value of user
     */
    public function setUser(User $user): Job
    {
        $user->addJob($this);

        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Set the value of client
     */
    public function setClient(Client $client): Job
    {
        $client->addJob($this);

        $this->client = $client;

        return $this;
    }

    /**
     * Get the value of payments
     */
    public function getPayments(): ArrayCollection|Collection
    {
        return $this->payments;
    }

    /**
     * Set the value of payments
     */
    public function addPayments(Payment $payments): Job
    {
        $this->payments->add($payments);

        return $this;
    }

    /**
     * Get the sum value of payments
     */
    public function getPaymentsTotal(): float
    {
        $total = 0;
        foreach ($this->payments as $payment) {
            $total += $payment->getAmountPaid();
        }

        return $total;
    }


    /**
     * Get the value of jobStatus
     */
    public function getJobStatus(): JobStatus
    {
        return $this->jobStatus;
    }

    /**
     * Set the value of jobStatus
     */
    public function setJobStatus(JobStatus $jobStatus): Job
    {
        $this->jobStatus = $jobStatus;

        return $this;
    }

    /**
     * Get the value of paystatus
     */
    public function getPaystatus(): ?Paystatus
    {
        return $this->paystatus;
    }

    /**
     * Set the value of paystatus
     */
    public function setPaystatus(?Paystatus $paystatus): Job
    {
        $this->paystatus = $paystatus;

        return $this;
    }
}