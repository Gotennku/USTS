<?php

namespace App\Enum;

enum PaymentStatus: string
{
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case PENDING = 'pending';
}
