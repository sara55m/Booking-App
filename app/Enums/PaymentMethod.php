<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case VISA = 'visa';
    case MASTERCARD = 'mastercard';
    case PAYPAL = 'paypal';
    case BANK_TRANSFER = 'bank_transfer';
    case WALLET = 'wallet';
}
