<?php

namespace App\Filament\Widgets;

use App\Enums\DealStage;
use App\Enums\LeadStatus;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeamOverviewWidget extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Active agents', User::role('agent')->where('status', UserStatus::Active)->count())
                ->color('primary'),
            Stat::make('Companies', Company::count())
                ->color('gray'),
            Stat::make('Open leads', Lead::whereNotIn('status', [LeadStatus::Won, LeadStatus::Lost])->count())
                ->color('info'),
            Stat::make('Open deals', Deal::whereNotIn('stage', [DealStage::Won, DealStage::Lost])->count())
                ->color('warning'),
        ];
    }
}
