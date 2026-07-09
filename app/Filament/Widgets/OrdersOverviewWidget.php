<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersOverviewWidget extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        $user = auth()->user();

        return ($user?->isAdmin() || $user?->isProduction()) ?? false;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Pending', Order::where('status', OrderStatus::Pending)->count())
                ->color('gray'),
            Stat::make('In Production', Order::where('status', OrderStatus::InProduction)->count())
                ->color('warning'),
            Stat::make('Ready to Dispatch', Order::where('status', OrderStatus::ReadyToDispatch)->count())
                ->color('info'),
            Stat::make('Dispatched', Order::where('status', OrderStatus::Dispatched)->count())
                ->color('primary'),
        ];
    }
}
