<?php

namespace App\Enums;

enum PropertyType: string
{
    case HOTEL = 'hotel';
    case APARTMENT = 'apartment';
    case VILLA = 'villa';
    case RESORT = 'resort';
    case CABIN = 'cabin';
    case HOSTEL = 'hostel';
}
