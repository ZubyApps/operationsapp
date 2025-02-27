<?php

declare(strict_types=1);

namespace App\Enum;

enum JobStatus: string
{
    case Pending    = 'Booked';
    case Inprogress = 'Inprogress';
    case Complete   = 'Delivered';
}
