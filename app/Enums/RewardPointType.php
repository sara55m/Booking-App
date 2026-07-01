<?php

namespace App\Enums;

enum RewardPointType:string
{
    case EARNED="earned";
    case REDEEMED="redeemed";
    case BONUS="bonus";
    case EXPIRED="expired";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
