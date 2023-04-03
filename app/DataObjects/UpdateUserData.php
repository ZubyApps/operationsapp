<?php

declare(strict_types = 1);

namespace App\DataObjects;

use App\Entity\Department;

class UpdateUserData
{
    public function __construct(
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $email,
        public readonly string $phonenumber,
        public readonly ?Department $department,
        public readonly ?string $password,
    ) {
    }
}
