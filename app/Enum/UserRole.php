<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRole: string
{
    case Unset      = 'Unset';
    case Creator    = 'Creator';
    case Editor     = 'Editor';
    case Admin      = 'Admin';
}
