<?php

namespace App\Filament\Widgets;

use App\Enums\DealStage;
use App\Models\Deal;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStatsWidget extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getStats(): array
    {
        $totalRevenue = Deal::where('stage', DealStage::Won)->sum('value');

        $thisMonthRevenue = Order::whereHas('deal', fn ($q) => $q->where('stage', DealStage::Won))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->with('deal')
            ->get()
            ->sum(fn (Order $order) => (float) $order->deal->value);

        $wonCount = Deal::where('stage', DealStage::Won)->count();
        $lostCount = Deal::where('stage', DealStage::Lost)->count();
        $decidedCount = $wonCount + $lostCount;
        $winRate = $decidedCount > 0 ? round(($wonCount / $decidedCount) * 100) : 0;

        $avgTurnaroundDays = Order::whereNotNull('delivered_at')
            ->get()
            ->map(fn (Order $order) => $order->created_at->diffInDays($order->delivered_at))
            ->average();

        return [
            Stat::make('Total revenue (Won deals)', number_format((float) $totalRevenue, 2))
                ->color('success'),
            Stat::make('Revenue this month', number_format($thisMonthRevenue, 2))
                ->color('success'),
            Stat::make('Win rate', "{$winRate}%")
                ->description("{$wonCount} won / {$lostCount} lost")
                ->color($winRate >= 50 ? 'success' : 'warning'),
            Stat::make('Avg. order turnaround', $avgTurnaroundDays !== null ? round($avgTurnaroundDays, 1) . ' days' : '—')
                ->description('Won to Delivered')
                ->color('info'),
        ];
    }
}
