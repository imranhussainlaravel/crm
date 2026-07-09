<?php

namespace App\Filament\Widgets;

use App\Enums\DealStage;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueByMonthChart extends ChartWidget
{
    protected ?string $heading = 'Revenue by month (last 6 months)';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => now()->subMonths($i)->startOfMonth());

        $revenueByMonth = Order::whereHas('deal', fn ($q) => $q->where('stage', DealStage::Won))
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->with('deal')
            ->get()
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m'))
            ->map(fn ($group) => $group->sum(fn (Order $order) => (float) $order->deal->value));

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $months->map(fn ($month) => $revenueByMonth->get($month->format('Y-m'), 0))->all(),
                    'borderColor' => '#0d9488',
                    'backgroundColor' => 'rgba(13, 148, 136, 0.15)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->format('M Y'))->all(),
        ];
    }
}
