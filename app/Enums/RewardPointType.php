<?php

namespace App\Enums;

enum RewardPointType:string
{
    case EARNED="earned";
    case REDEEMED="redeemed";
    case REVERSED = 'reversed';
    case RETURNED = 'returned';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
