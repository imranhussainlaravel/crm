<?php

namespace App\Filament\Widgets;

use App\Enums\DealStage;
use App\Models\Deal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MyPipelineWidget extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->isAgent() ?? false;
    }

    protected function getStats(): array
    {
        $userId = auth()->id();

        $pipelineValue = Deal::where('sales_rep_id', $userId)
            ->whereIn('stage', [DealStage::Quoted, DealStage::Negotiation])
            ->sum('value');

        $wonCount = Deal::where('sales_rep_id', $userId)->where('stage', DealStage::Won)->count();
        $lostCount = Deal::where('sales_rep_id', $userId)->where('stage', DealStage::Lost)->count();
        $decidedCount = $wonCount + $lostCount;
        $conversionRate = $decidedCount > 0 ? round(($wonCount / $decidedCount) * 100) : 0;

        return [
            Stat::make('My open pipeline value', number_format((float) $pipelineValue, 2))
                ->description('Quoted + Negotiation deals')
                ->color('info'),
            Stat::make('My conversion rate', "{$conversionRate}%")
                ->description("{$wonCount} won / {$lostCount} lost")
                ->color($conversionRate >= 50 ? 'success' : 'warning'),
        ];
    }
}
