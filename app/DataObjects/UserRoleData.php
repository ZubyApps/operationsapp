<?php

declare(strict_types = 1);

namespace App\DataObjects;

use App\Entity\User;
use App\Enum\UserRole;

class UserRoleData
{
    public function __construct(
        public readonly User        $user,
        public readonly UserRole  $userRole,
    ) {
    }
}
