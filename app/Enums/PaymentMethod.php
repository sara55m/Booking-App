<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum PaymentMethod: string implements HasLabel,HasColor
{
    case CASH = 'cash';
    case CARD = 'card';
    case VISA = 'visa';
    case MASTERCARD = 'mastercard';
    case PAYPAL = 'paypal';
    case BANK_TRANSFER = 'bank_transfer';
    case WALLET = 'wallet';

    public function getLabel(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
            self::VISA => 'Visa',
            self::MASTERCARD => 'MasterCard',
            self::PAYPAL => 'PayPal',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::WALLET => 'Wallet',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CASH => 'gray',
            self::CARD => 'primary',
            self::VISA => 'info',
            self::MASTERCARD => 'warning',
            self::PAYPAL => 'success',
            self::BANK_TRANSFER => 'purple',
            self::WALLET => 'teal',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    
}
