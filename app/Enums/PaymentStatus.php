<?php

namespace App\Enums;
use Filament\Support\Contracts\HasColor;

enum PaymentStatus : string implements HasColor
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case FORFEITED = 'forfeited';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'primary',
            self::FORFEITED=>'gray',
        };
    }
}
