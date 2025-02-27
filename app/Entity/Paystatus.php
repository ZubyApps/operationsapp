<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\HasTimestamps;
use App\Enum\BillStatus;
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

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of job
     */
    public function getJob(): ?Job
    {
        return $this->job;
    }

    /**
     * Set the value of job
     */
    public function setJob(?Job $job): Paystatus
    {
        $this->job = $job;

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
    public function setUser(User $user): Paystatus
    {
        $user->addPaystatus($this);

        $this->user = $user;

        return $this;
    }

    // /**
    //  * Get the value of percentPaid
    //  */
    // public function getPercentPaid(): float
    // {
    //     return $this->percentPaid;
    // }

    // /**
    //  * Set the value of percentPaid
    //  */
    // public function setPercentPaid(float $percentPaid): Paystatus
    // {
    //     $this->percentPaid = $percentPaid;

    //     return $this;
    // }

    // /**
    //  * Get the value of billStatus
    //  */
    // public function getBillStatus(): BillStatus
    // {
    //     return $this->billStatus;
    // }

    // /**
    //  * Set the value of billStatus
    //  */
    // public function setBillStatus(BillStatus $billStatus): Paystatus
    // {
    //     $this->billStatus = $billStatus;

    //     return $this;
    // }

    /**
     * Get the value of totalPaid
     */
    public function getTotalPaid(): float
    {
        return $this->totalPaid;
    }

    /**
     * Set the value of totalPaid
     */
    public function setTotalPaid(float $totalPaid): Paystatus
    {
        $this->totalPaid = $totalPaid;

        return $this;
    }
}