<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use App\Models\Payment;
use App\Filament\Resources\Bookings\BookingResource;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_booking')
                    ->label(__('messages.view_booking'))
                    ->icon('heroicon-o-calendar')
                    ->url(fn (Payment $record) => BookingResource::getUrl('view', [
                        'record' => $record->booking,
                    ]))
                    ->openUrlInNewTab(false),
        ];
    }
}
