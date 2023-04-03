<?php

declare(strict_types = 1);

namespace App\DataObjects;

class ClientData
{
    public function __construct(
        public readonly string $name,
        public readonly string $number,
        public readonly ?string $email,
        public readonly ?string $city,
        public readonly ?string $state,
        public readonly ?string $country,
    ) {
    }
}
