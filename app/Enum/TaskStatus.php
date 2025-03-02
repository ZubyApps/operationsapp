<?php

declare(strict_types=1);

namespace App\Enum;

enum TaskStatus: string
{
    case Pending    = 'Pending';
    case Ongoing    = 'Ongoing';
    case Finished   = 'Finished';
}
