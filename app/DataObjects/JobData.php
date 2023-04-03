<?php

declare(strict_types = 1);

namespace App\DataObjects;

use App\Entity\Client;
use App\Entity\Jobtype;
use App\Enum\JobStatus;
use DateTime;

class JobData
{
    public function __construct(
        public readonly Client          $client,
        public readonly Jobtype         $type,
        public readonly string          $details,
        public readonly ?DateTime       $dueDate,
        public readonly float           $bill,
        public readonly JobStatus       $jobStatus,
    ) {
    }
}
