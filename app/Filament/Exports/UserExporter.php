<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use Illuminate\Database\Eloquent\Builder;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->withCount([
            'bookings',
            'favoriteProperties',
        ]);
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label(__("messages.id")),
            ExportColumn::make('name')->label(__("messages.name")),
            ExportColumn::make('email')->label(__("messages.email")),
            ExportColumn::make('phone')->label(__("messages.phone")),
            ExportColumn::make('role')->label(__("messages.role")),
            ExportColumn::make('reward_points')->label(__("messages.points")),
            ExportColumn::make('bookings_count')
            ->label(__("messages.bookings")),
            ExportColumn::make('favorite_properties_count')
            ->label(__("messages.favorites")),
            ExportColumn::make('email_verified_at')->label(__("messages.email_verified_at")),
            ExportColumn::make('created_at')->label(__("messages.created_at")),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
