<?php

declare(strict_types = 1);

namespace App\DataObjects;

use App\Entity\Category;
use App\Entity\Sponsor;
use DateTime;

class ExpenseData
{
    public function __construct(
        public readonly Category $category,
        public readonly Sponsor $sponsor,
        public readonly DateTime $date,
        public readonly string $description,
        public readonly float  $amount,
    ) {
    }
}
