<?php

namespace App\Filament\Widgets;

use App\Enums\DealStage;
use App\Models\Deal;
use Filament\Widgets\ChartWidget;

class RevenueByAgentChart extends ChartWidget
{
    protected ?string $heading = 'Revenue by agent';

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
            ->whereNotNull('sales_rep_id')
            ->with('salesRep')
            ->get()
            ->groupBy(fn (Deal $deal) => $deal->salesRep?->name ?? 'Unassigned')
            ->map(fn ($group) => (float) $group->sum('value'));

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $rows->values()->all(),
                    'backgroundColor' => '#0d9488',
                ],
            ],
            'labels' => $rows->keys()->all(),
        ];
    }
}
