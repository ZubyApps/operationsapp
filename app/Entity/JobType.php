<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\HasTimestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity(), Table('jobtypes')]
#[HasLifecycleCallbacks]
class JobType
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column]
    private string $name;

    #[Column(nullable: \true)]
    private string $description;

    #[OneToMany(mappedBy: 'jobType', targetEntity: Job::class)]
    private Collection $jobs;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
    }

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     */
    public function setName(string $name): JobType
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set the value of description
     */
    public function setDescription(string $description): JobType
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of jobs
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    /**
     * Set the value of jobs
     */
    public function addJob(Job $job): JobType
    {
        $this->jobs->add($job);

        return $this;
    }

    /**
     * Get the sum value of payments
     */
    public function getJobPaymentsTotal(): float
    {
        $total = 0;
        foreach ($this->jobs as $job) {
            foreach($job->getPayments() as $payment )
            $total += $payment->getAmountPaid();
        }

        return $total;
    }
}