<?php

namespace App\Enums;

enum BookingCancellationReason: string
{
    case CUSTOMER_REQUESTED = 'customer_requested';
    case PAYMENT_EXPIRED = 'payment_expired';       
    case BALANCE_UNPAID = 'balance_unpaid';      
    
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}