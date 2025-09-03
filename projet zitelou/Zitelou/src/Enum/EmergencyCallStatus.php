<?php

namespace App\Enum;

enum EmergencyCallStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case MISSED = 'missed';
}
