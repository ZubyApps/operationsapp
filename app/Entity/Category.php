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

#[Entity(), Table('category')]
#[HasLifecycleCallbacks]
class Category
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column]
    private string $name;

    #[Column(nullable: \true)]
    private string $description;

    #[OneToMany(mappedBy: 'category', targetEntity: Expense::class)]
    private Collection $expenses;

    #[ManyToOne(inversedBy: 'categories')]
    private User|null $user;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
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
    public function setName(string $name): Category
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
    public function setDescription(string $description): Category
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of jobs
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    /**
     * Set the value of jobs
     */
    public function addExpense(Expense $expense): Category
    {
        $this->expenses->add($expense);

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
    public function setUser(User $user): Category
    {
        $user->addCategory($this);

        $this->user = $user;

        return $this;
    }

    /**
     * Get the sum value of expenses
     */
    public function getExpenseTotal(): float
    {
        $total = 0;
        foreach ($this->expenses as $expense) {
            $total += $expense->getAmount();
        }

        return $total;
    }
}