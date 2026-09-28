<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Payment;
use App\Enums\PaymentStatus;

class PaymentsStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totallyPaidPayments = Payment::where('status',PaymentStatus::PAID)->count() ?? 0;

        $failedPayments = Payment::where('status',PaymentStatus::FAILED)->count() ?? 0;

        $refundedPayments = Payment::where('status',PaymentStatus::REFUNDED)->count() ?? 0;
        return [
            //Number of Totally Paid Payments
            Stat::make('Totally Paid',$totallyPaidPayments)
                ->label(__('messages.totally_paid'))
                ->description(__('messages.totally_paid_description'))
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            //Failed Payments
            Stat::make('Failed',$failedPayments)
                ->label(__('messages.failed_payments'))
                ->description(__('messages.failed_payments_description'))
                ->color('danger')
                ->icon('heroicon-o-currency-dollar'),


            //Refunded Payments
            Stat::make('Refunded',$refundedPayments)
                ->label(__('messages.refunded'))
                ->description(__('messages.refunded_description'))
                ->color('warning')
                ->icon('heroicon-o-currency-dollar'),
        ];

    }
}
