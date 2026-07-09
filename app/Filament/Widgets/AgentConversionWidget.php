<?php

namespace App\Filament\Widgets;

use App\Enums\DealStage;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AgentConversionWidget extends TableWidget
{
    protected static ?string $heading = 'Conversion rate by agent';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->role('agent')
                    ->withCount([
                        'deals as won_count' => fn (Builder $q) => $q->where('stage', DealStage::Won),
                        'deals as lost_count' => fn (Builder $q) => $q->where('stage', DealStage::Lost),
                    ])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Agent')
                    ->weight('bold'),
                TextColumn::make('won_count')
                    ->label('Won')
                    ->color('success'),
                TextColumn::make('lost_count')
                    ->label('Lost')
                    ->color('danger'),
                TextColumn::make('conversion_rate')
                    ->label('Conversion rate')
                    ->state(function (User $record): string {
                        $decided = $record->won_count + $record->lost_count;

                        return $decided > 0
                            ? round(($record->won_count / $decided) * 100) . '%'
                            : '—';
                    }),
            ])
            ->paginated(false);
    }
}
