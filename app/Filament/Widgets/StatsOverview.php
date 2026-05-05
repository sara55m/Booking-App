<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Payment;
use App\Models\Booking;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::where('role','user')->count() ?? 0;
        $totalRevenue = Payment::sum('amount') ?? 0;
        $totalRevenueThisYear = Payment::whereYear('paid_at', now()->year)->sum('amount') ?? 0;
        $totalRevenueThisMonth = Payment::whereYear('paid_at', now()->year)->whereMonth('paid_at', now()->month)->sum('amount') ?? 0;
        $totalBookings = Booking::count() ?? 0;
        $pendingBookings = Booking::where('status','pending')->count() ?? 0;
        $confirmedBookings = Booking::where('status','confirmed')->count() ?? 0;
        return [
            //Total Users
            Stat::make('Total Users', $totalUsers)
                ->label(__('messages.all_registered_users'))
                ->color('success')
                ->icon('heroicon-o-users'),

            //Total Revenue
            Stat::make('Total Revenue',$totalRevenue. 'EGP')
                ->label(__('messages.total_revenue'))
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            //Total Revenue this year
            Stat::make('Revenue This Year',$totalRevenueThisYear. ' EGP')
                ->label(__('messages.revenue_this_year'))
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            //Total Revenue this month
            Stat::make('Revenue This Month',$totalRevenueThisMonth. ' EGP')
                ->label(__('messages.revenue_this_month'))
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            //Total Bookings
            Stat::make('Total Bookings', $totalBookings)
                ->label(__('messages.total_bookings'))
                ->color('success')
                ->icon('heroicon-o-calendar'),

            //Total Pending Bookings
            Stat::make('Pending Bookings', $pendingBookings)
                ->label(__('messages.pending_bookings'))
                ->color('warning')
                ->icon('heroicon-o-clock'),

            //Total Confirmed Bookings
            Stat::make('Confirmed Bookings', $confirmedBookings)
                ->label(__('messages.confirmed_bookings'))
                ->color('success')
                ->icon('heroicon-o-check'),
        ];
    }
}
