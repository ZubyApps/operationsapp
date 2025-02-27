<?php

declare(strict_types = 1);

namespace App\DataObjects;

use App\Entity\Job;
use App\Entity\Paymethod;

class PayStatusData
{
    public function __construct(
        public readonly Job $job,
    ) {
    }
}
