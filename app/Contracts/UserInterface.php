<?php

declare(strict_types = 1);

namespace App\Contracts;

use App\Enum\UserRole;

interface UserInterface
{
    public function getId(): int;
    public function getPassword(): string;
    public function getFirstname(): string;
    public function getUserRole(): ?UserRole;
}
