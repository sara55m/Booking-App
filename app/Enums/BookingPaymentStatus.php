<?php

namespace App\Enums;

enum BookingPaymentStatus : string
{
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case UNPAID = 'unpaid';
}
