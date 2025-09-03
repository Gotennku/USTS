<?php

namespace App\Enum;

enum SubscriptionEvent: string
{
    case CREATED = 'created';
    case RENEWED = 'renewed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
}
