<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Payment;

class PaymentsStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totallyPaidPayments = Payment::where('status','paid')->count() ?? 0;
        $partiallyPaidPayments = Payment::where('status','partial')->count() ?? 0;
        $refundedPayments = Payment::where('status','refunded')->count() ?? 0;
        return [
            //Number of Totally Paid Payments
            Stat::make('Totally Paid',$totallyPaidPayments)
                ->label(__('messages.totally_paid'))
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            //Partially Paid Payments
            Stat::make('Partially Paid',$partiallyPaidPayments)
                ->label(__('messages.partially_paid'))
                ->color('warning')
                ->icon('heroicon-o-currency-dollar'),



            //Refunded Payments
            Stat::make('Refunded',$refundedPayments)
                ->label(__('messages.refunded'))
                ->color('danger')
                ->icon('heroicon-o-currency-dollar'),
        ];

    }
}
