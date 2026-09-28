<?php

namespace App\Enums;
use Filament\Support\Contracts\HasColor;

enum BookingCancellationReason: string implements HasColor
{
    case CUSTOMER_REQUESTED = 'customer_requested';
    case PAYMENT_EXPIRED = 'payment_expired';
    case BALANCE_UNPAID = 'balance_unpaid';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CUSTOMER_REQUESTED => 'gray',
            self::BALANCE_UNPAID => 'warning',
            self::PAYMENT_EXPIRED => 'danger',
        };
    }
}
