<?php

namespace App\Enums;

enum OfferStatus: string
{
    case ACTIVE = 'active';
    case UPCOMING = 'upcoming';
    case EXPIRED = 'expired';
    case DISABLED = 'disabled';

    public function label(): string
    {
        return __("offers.status.{$this->value}");
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::UPCOMING => 'warning',
            self::EXPIRED => 'danger',
            self::DISABLED => 'gray',
        };
    }
}