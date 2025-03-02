<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contracts\UserInterface;
use App\Entity\Traits\HasTimestamps;
use App\Enum\UserRole;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity(), Table('users')]
#[HasLifecycleCallbacks]
class User implements UserInterface
{
    use HasTimestamps;
    
    #[Id, Column(options: ['unsigned' => true]), GeneratedValue()]
    private int $id;

    #[Column(unique:\true)]
    private string $email;

    #[Column()]
    private string $firstname;

    #[Column()]
    private string $lastname;

    #[Column(unique:\true, nullable:\true,)]
    private string $phoneNumber;

    #[Column()]
    private string $password;

    #[ManyToOne(inversedBy: 'users', targetEntity: Department::class)]
    #[JoinColumn(name: 'department_id', referencedColumnName: 'id')]
    private Department|null $department;

    #[Column(nullable: \true)]
    private UserRole|null $userRole;

    #[OneToMany(mappedBy: 'head', targetEntity: Department::class, cascade:['remove'])]
    private Collection $departments;

    #[OneToMany(mappedBy: 'user', targetEntity: Client::class)]
    private Collection $clients;

    #[OneToMany(mappedBy: 'user', targetEntity: Job::class)]
    private Collection $jobs;

    #[OneToMany(mappedBy: 'user', targetEntity: Payment::class)]
    private Collection $payments;

    #[OneToMany(mappedBy: 'user', targetEntity: Paystatus::class)]
    private Collection $paystatus;

    #[OneToMany(mappedBy: 'user', targetEntity: Expense::class)]
    private Collection $expenses;

    #[OneToMany(mappedBy: 'user', targetEntity: Category::class)]
    private Collection $categories;

    #[OneToMany(mappedBy: 'user', targetEntity: Sponsor::class)]
    private Collection $sponsors;

    #[OneToMany(mappedBy: 'user', targetEntity: Task::class)]
    private Collection $tasks;

    #[OneToMany(mappedBy: 'assignedTo', targetEntity: Task::class, cascade:['remove'])]
    private Collection $assignedTos;


    public function __construct()
    {
        $this->clients      = new ArrayCollection();
        $this->jobs         = new ArrayCollection();
        $this->payments     = new ArrayCollection();
        $this->paystatus    = new ArrayCollection();
        $this->expenses     = new ArrayCollection();
        $this->categories   = new ArrayCollection();
        $this->sponsors     = new ArrayCollection();
        $this->tasks        = new ArrayCollection();
        $this->assignedTos  = new ArrayCollection();
    }

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
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
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of firstname
     */
    public function getFirstname(): string
    {
        if (! isset($this->firstname)){
            return $this->firstname = 'Not assigned';
        }
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     */
    public function setFirstname(string $firstname): User
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     */
    public function setLastname(string $lastname): User
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the value of password
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        if (! isset($this->department)){
            return $this->department = new Department();
        }
        return $this->department;
    }

    public function setDepartment(?Department $department): User
    {
        if (!$department) {
            $this->department = $department;

            return $this;
        }
        
        $department->addUser($this);

        $this->department = $department;

        return $this;
    }

    public function getDepartments(): ArrayCollection|Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): User
    {
        $this->departments->add($department);

        return $this;
    }

    /**
     * Get the value of clients
     */
    public function getClients(): ArrayCollection|Collection
    {
        return $this->clients;
    }

    /**
     * Set the value of clients
     */
    public function addClient(Client $client): User
    {
        $this->clients->add($client);

        return $this;
    }

    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): User
    {
        $this->jobs->add($job);

        return $this;
    }

    /**
     * Get the value of payments
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    /**
     * Set the value of payments
     */
    public function addPayment(Payment $payment): User
    {
        $this->payments->add($payment);

        return $this;
    }

    /**
     * Get the value of paystatus
     */
    public function getPaystatus(): Collection
    {
        return $this->paystatus;
    }

    /**
     * Set the value of paystatus
     */
    public function addPaystatus(Paystatus $paystatus): User
    {
        $this->paystatus->add($paystatus);

        return $this;
    }

    /**
     * Get the value of expenses
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    /**
     * Set the value of expenses
     */
    public function addExpense(Expense $expense): User
    {
        $this->expenses->add($expense);

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
    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get the value of userRole
     */
    public function getUserRole(): ?UserRole
    {
        if (! isset($this->userRole)) {
            return $this->userRole = UserRole::from('Unset');
        }
        return $this->userRole;
    }

    /**
     * Set the value of userRole
     */
    public function setUserRole(?UserRole $userRole): User
    {
        $this->userRole = $userRole;

        return $this;
    }

    /**
     * Get the value of expenses
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * Set the value of expenses
     */
    public function addCategory(Category $category): User
    {
        $this->categories->add($category);

        return $this;
    }

    public function getSponsors(): Collection
    {
        return $this->categories;
    }

    public function addSponsor(Sponsor $sponsor): User
    {
        $this->sponsors->add($sponsor);

        return $this;
    }

    public function getTasks(): Collection
    {
        return $this->categories;
    }

    public function addTask(Task $task): User
    {
        $this->tasks->add($task);

        return $this;
    }

    public function getAssigedTos(): ArrayCollection|Collection
    {
        return $this->assignedTos;
    }

    public function addAssignedTo(Task $assignedTo): User
    {
        $this->assignedTos->add($assignedTo);

        return $this;
    }

}