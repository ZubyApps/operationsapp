<?php

declare(strict_types = 1);

namespace App\DataObjects;

use App\Entity\Job;
use App\Entity\Paymethod;
use DateTime;

class PaymentData
{
    public function __construct(
        public readonly float $paid,
        public readonly DateTime $date,
        public readonly Job $job,
        public readonly Paymethod $paymethod,
    ) {
    }
}
