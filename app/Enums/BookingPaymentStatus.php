<?php

namespace App\Enums;

enum BookingPaymentStatus : string
{
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case REFUNDED='refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
