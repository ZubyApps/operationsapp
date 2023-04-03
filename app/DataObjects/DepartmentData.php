<?php

declare(strict_types = 1);

namespace App\DataObjects;

use App\Entity\User;

class DepartmentData
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly User $head,
    ) {
    }
}
