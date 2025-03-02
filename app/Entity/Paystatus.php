<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\HasTimestamps;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(), Table('paystatus')]
#[HasLifecycleCallbacks]
class Paystatus
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(type: Types::DECIMAL, precision: 13, scale: 3, nullable: \true)]
    private float|null $totalPaid;

    #[OneToOne(targetEntity: Job::class, inversedBy: 'paystatus')]
    #[JoinColumn(onDelete:'CASCADE')]
    private Job $job;

    #[ManyToOne(inversedBy: 'paystatus')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): Paystatus
    {
        $this->job = $job;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Paystatus
    {
        $user->addPaystatus($this);

        $this->user = $user;

        return $this;
    }

    public function getTotalPaid(): float
    {
        return $this->totalPaid;
    }

    public function setTotalPaid(float $totalPaid): Paystatus
    {
        $this->totalPaid = $totalPaid;

        return $this;
    }
}