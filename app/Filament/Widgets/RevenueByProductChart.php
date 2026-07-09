<?php

namespace App\Filament\Widgets;

use App\Enums\DealStage;
use App\Models\Deal;
use Filament\Widgets\ChartWidget;

class RevenueByProductChart extends ChartWidget
{
    protected ?string $heading = 'Revenue by product';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = Deal::where('stage', DealStage::Won)
            ->with('lead')
            ->get()
            ->groupBy(fn (Deal $deal) => $deal->lead?->product_interest?->getLabel() ?? 'Unknown')
            ->map(fn ($group) => (float) $group->sum('value'));

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $rows->values()->all(),
                    'backgroundColor' => '#0891b2',
                ],
            ],
            'labels' => $rows->keys()->all(),
        ];
    }
}
