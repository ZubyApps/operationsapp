<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\HasTimestamps;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(), Table('payments')]
#[HasLifecycleCallbacks]
class Payment
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(type: Types::DECIMAL, precision: 13, scale: 3)]
    private float $amountPaid;
    
    #[Column(nullable: \true)]
    private DateTime $date;

    #[ManyToOne(inversedBy: 'payments')]
    private Paymethod $payMethod;

    #[ManyToOne(inversedBy: 'payments')]
    private Client $client;

    #[ManyToOne(inversedBy: 'payments')]
    private Job $job;

    #[ManyToOne(inversedBy: 'payments')]
    private User $user;

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of amountPaid
     */
    public function getAmountPaid(): float
    {
        return $this->amountPaid;
    }

    /**
     * Set the value of amountPaid
     */
    public function setAmountPaid(float $amountPaid): Payment
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    /**
     * Get the value of date
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * Set the value of date
     */
    public function setDate(DateTime $date): Payment
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get the value of jobType
     */
    public function getPayMethod(): Paymethod
    {
        return $this->payMethod;
    }

    /**
     * Set the value of jobType
     */
    public function setPayMethod(PayMethod $payMethod): Payment
    {
        $payMethod->addPayment($this);

        $this->payMethod = $payMethod;

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
    public function setUser(User $user): Payment
    {
        $user->addPayment($this);

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
    public function setClient(Client $client): Payment
    {
        $client->addPayments($this);

        $this->client = $client;

        return $this;
    }

    /**
     * Get the value of job
     */
    public function getJob(): Job
    {
        return $this->job;
    }

    /**
     * Set the value of job
     */
    public function setJob(Job $job): Payment
    {
        $job->addPayments($this);

        $this->job = $job;

        return $this;
    }
}