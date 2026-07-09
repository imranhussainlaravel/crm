<?php

namespace App\Filament\Widgets;

use App\Enums\DealStage;
use App\Models\Company;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopClientsWidget extends TableWidget
{
    protected static ?string $heading = 'Top clients by revenue';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->select('companies.*')
                    ->selectRaw('sum(deals.value) as revenue')
                    ->join('contacts', 'contacts.company_id', '=', 'companies.id')
                    ->join('leads', 'leads.contact_id', '=', 'contacts.id')
                    ->join('deals', 'deals.lead_id', '=', 'leads.id')
                    ->where('deals.stage', DealStage::Won)
                    ->groupBy('companies.id')
                    ->orderByDesc('revenue')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Company')
                    ->weight('bold'),
                TextColumn::make('revenue')
                    ->label('Revenue')
                    ->money()
                    ->color('success'),
            ])
            ->paginated(false);
    }
}
