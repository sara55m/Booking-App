<?php

namespace App\Enums;

enum RewardPointType:string
{
    case EARNED="earned";
    case REDEEMED="redeemed";
    case BONUS="bonus";
    case EXPIRED="expired";
}
