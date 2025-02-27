<?php

declare(strict_types = 1);

namespace App\DataObjects;

class RegisterUserData
{
    public function __construct(
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $email,
        public readonly string $password,
        public readonly string $phonenumber,
    ) {
    }
}
