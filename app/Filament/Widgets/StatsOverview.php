<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Payment;
use App\Models\Booking;
use App\Enums\PaymentStatus;
use App\Enums\BookingStatus;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::where('role','user')->count() ?? 0;

        $totalRevenue = Payment::where('status',PaymentStatus::PAID)->sum('amount') ?? 0;

        $totalRevenueThisYear = Payment::where('status',PaymentStatus::PAID)->whereYear('paid_at', now()->year)->sum('amount') ?? 0;

        $totalRevenueThisMonth = Payment::where('status',PaymentStatus::PAID)->whereYear('paid_at', now()->year)->whereMonth('paid_at', now()->month)->sum('amount') ?? 0;

        $totalBookings = Booking::count() ?? 0;

        $pendingBookings = Booking::where('status',BookingStatus::PENDING)->count() ?? 0;

        $confirmedBookings = Booking::where('status',BookingStatus::CONFIRMED)->count() ?? 0;

        $currency=config('app.currency');

        return [
            //Total Users
            Stat::make('Total Users', $totalUsers)
                ->label(__('messages.all_registered_users'))
                ->description(__('messages.all_registered_users_description'))
                ->color('success')
                ->icon('heroicon-o-users'),

            //Total Revenue
            Stat::make('Total Revenue',$totalRevenue.' '.$currency)
                ->label(__('messages.total_revenue'))
                ->description(__('messages.total_revenue_description'))
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            //Total Revenue this year
            Stat::make('Revenue This Year',$totalRevenueThisYear.' '.$currency)
                ->label(__('messages.revenue_this_year'))
                ->description(__('messages.revenue_this_year_description'))
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            //Total Revenue this month
            Stat::make('Revenue This Month',$totalRevenueThisMonth.' '.$currency)
                ->label(__('messages.revenue_this_month'))
                ->description(__('messages.revenue_this_month_description'))
                ->color('info')
                ->icon('heroicon-o-currency-dollar'),

            //Total Bookings
            Stat::make('Total Bookings', $totalBookings)
                ->label(__('messages.total_bookings'))
                ->description(__('messages.total_bookings_description'))
                ->color('info')
                ->icon('heroicon-o-calendar'),

            //Total Pending Bookings
            Stat::make('Pending Bookings', $pendingBookings)
                ->label(__('messages.pending_bookings'))
                ->description(__('messages.pending_bookings_description'))
                ->color('warning')
                ->icon('heroicon-o-clock'),

            //Total Confirmed Bookings
            Stat::make('Confirmed Bookings', $confirmedBookings)
                ->label(__('messages.confirmed_bookings'))
                ->description(__('messages.confirmed_bookings_description'))
                ->color('success')
                ->icon('heroicon-o-check'),
        ];
    }
}
