<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Booking;

class BookingsChart extends ChartWidget
{
    protected ?string $heading = null;

    public function getHeading(): string
    {
        return __('messages.bookings_over_time');
    }
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        if ($data->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => __('messages.bookings'),
                        'data' => [0],
                    ],
                ],
                'labels' => [__('messages.no_data')],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => __('messages.bookings'),
                    'data' => $data->values(),
                ],
            ],
            'labels' => $data->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
