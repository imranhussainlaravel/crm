<?php

namespace App\Filament\Widgets;

use App\Enums\DealStage;
use App\Models\Deal;
use Filament\Widgets\ChartWidget;

class LostReasonChart extends ChartWidget
{
    protected ?string $heading = 'Lost reason breakdown';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $rows = Deal::where('stage', DealStage::Lost)
            ->whereNotNull('lost_reason')
            ->get()
            ->groupBy(fn (Deal $deal) => $deal->lost_reason->getLabel())
            ->map(fn ($group) => $group->count());

        return [
            'datasets' => [
                [
                    'data' => $rows->values()->all(),
                    'backgroundColor' => ['#ef4444', '#f97316', '#eab308', '#a855f7', '#64748b'],
                ],
            ],
            'labels' => $rows->keys()->all(),
        ];
    }
}
