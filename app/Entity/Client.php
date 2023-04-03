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
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity(), Table('clients')]
#[HasLifecycleCallbacks]
class Client
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column]
    private string $name;
    
    #[Column]
    private string $phoneNumber;

    #[Column(nullable:\true)]
    private string $email;

    #[Column(nullable: \true)]
    private string $city;

    #[Column(nullable: \true)]
    private string $state;

    #[Column(nullable: \true)]
    private string $country;

    #[ManyToOne(inversedBy: 'clients')]
    private User $user;

    #[OneToMany(mappedBy: 'client', targetEntity: Job::class)]
    private Collection $jobs;

    #[OneToMany(mappedBy: 'client', targetEntity: Payment::class)]
    private Collection $payments;

    #[OneToMany(mappedBy: 'client', targetEntity: Paystatus::class)]
    private Collection $paystatus;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->paystatus = new ArrayCollection();
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
    public function setName(string $name): Client
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the value of email
     */
    public function setEmail(string $email): Client
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of phoneNumber
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * Set the value of phoneNumber
     */
    public function setPhoneNumber(string $phoneNumber): Client
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get the value of city
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Set the value of city
     */
    public function setCity(string $city): Client
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the value of state
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Set the value of state
     */
    public function setState(string $state): Client
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the value of country
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Set the value of country
     */
    public function setCountry(string $country): Client
    {
        $this->country = $country;

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
    public function setUser(User $user): Client
    {
        $user->addClient($this);

        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of jobs
     */
    public function getJobs(): ArrayCollection|Collection
    {
        return $this->jobs;
    }

    /**
     * Set the value of jobs
     */
    public function addJob(Job $jobs): Client
    {
        $this->jobs->add($jobs);

        return $this;
    }

    /**
     * Get the sum value of payments
     */
    public function getTotalBills(): float
    {
        $total = 0;
        foreach ($this->jobs as $job) {
            $total += $job->getAmountDue();
        }

        return $total;
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
    public function addPayments(Payment $payments): Client
    {
        $this->payments->add($payments);

        return $this;
    }

    /**
     * Get the sum value of payments
     */
    public function getTotalPayments(): float
    {
        $total = 0;
        foreach ($this->payments as $payment) {
            $total += $payment->getAmountPaid();
        }

        return $total;
    }

    /**
     * Get the value of paystatus
     */
    public function getPaystatus(): ArrayCollection|Collection
    {
        return $this->paystatus;
    }

    /**
     * Set the value of paystatus
     */
    public function addPaystatus(Paystatus $paystatus): Client
    {
        $this->paystatus->add($paystatus);

        return $this;
    }
}