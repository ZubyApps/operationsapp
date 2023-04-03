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
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity(), Table('departments')]
#[HasLifecycleCallbacks]
class Department
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(nullable: \true)]
    private string|null $name;

    #[Column(nullable: \true)]
    private string|null $description;

    #[ManyToOne(inversedBy: 'departments')]
    #[JoinColumn(onDelete: 'CASCADE')]
    private User $head;

    #[OneToMany(mappedBy: 'department', targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        if (! isset($this->id)){
            return $this->id = 0;
        }

        return $this->id;
    }


    /**
     * Get the value of name
     */
    public function getName(): string
    {
        if (!isset($this->name)){
            return $this->name = 'Not assigned';
        }
        return $this->name;
    }

    /**
     * Set the value of name
     */
    public function setName(string $name): self
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
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


    /**
     * Get the value of user
     */
    public function getUsers(): ArrayCollection|Collection
    {
        return $this->users;
    }

    /**
     * Set the value of user
     */
    public function addUser(User $user): Department
    {
        $this->users->add($user);

        return $this;
    }

    /**
     * Get the value of head
     */
    public function getHead(): User
    {
        if (! isset($this->head)){
            $this->head = new User();
        }
        return $this->head;
    }

    /**
     * Set the value of head
     */
    public function setHead(User $head): Department
    {
        $head->addDepartment($this);

        $this->head = $head;

        return $this;
    }
}