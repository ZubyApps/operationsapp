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

#[Entity(), Table('expenses')]
#[HasLifecycleCallbacks]
class Expense
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[ManyToOne(inversedBy: 'expenses')]
    private Sponsor $sponsor;

    #[ManyToOne(inversedBy: 'expenses')]
    private Category $category;

    #[Column(nullable: \true)]
    private string $description;

    #[Column(type: Types::DECIMAL, precision: 13, scale: 3, nullable: \true)]
    private float|null $amount;

    #[Column(nullable: \true)]
    private DateTime|null $date;

    #[ManyToOne(inversedBy: 'expenses')]
    private User|null $user;

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of type
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * Set the value of type
     */
    public function setCategory(Category $category): Expense
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the value of sponsor
     */
    public function getSponsor(): Sponsor
    {
        return $this->sponsor;
    }

    /**
     * Set the value of sponsor
     */
    public function setSponsor(Sponsor $sponsor): Expense
    {
        $this->sponsor = $sponsor;

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
    public function setDescription(string $description): Expense
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of amount
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Set the value of amount
     */
    public function setAmount(float $amount): Expense
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of date
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * Set the value of date
     */
    public function setDate(?DateTime $date): self
    {
        $this->date = $date;

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
    public function setUser(User $user): Expense
    {
        $user->addExpense($this);

        $this->user = $user;

        return $this;
    }
}