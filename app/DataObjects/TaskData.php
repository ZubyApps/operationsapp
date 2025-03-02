<?php

declare(strict_types = 1);

namespace App\DataObjects;

use App\Entity\User;
use App\Enum\TaskStatus;
use DateTime;

class TaskData
{
    public function __construct(
        public readonly ?string $taskComment,
        public readonly ?User $assignedTo,
        public readonly ?DateTime $deadline,
        // public readonly ?string $inprogressComment,
        // public readonly ?string $completedComment,
        // public readonly ?Job $job,
        // public readonly ?DateTime $inprogressDate,
        // public readonly ?DateTime $completedDated,
        // public readonly ?TaskStatus $taskStatus,
    ) {
    }
}
