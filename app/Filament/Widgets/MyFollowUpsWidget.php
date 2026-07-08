<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MyFollowUpsWidget extends TableWidget
{
    protected static ?string $heading = 'Follow-ups due';

    public static function canView(): bool
    {
        $user = auth()->user();

        return ($user?->isAdmin() || $user?->isAgent()) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => LeadResource::getEloquentQuery()
                ->whereNotNull('follow_up_date')
                ->whereDate('follow_up_date', '<=', now())
                ->with(['contact.company', 'assignedAgent']))
            ->defaultSort('follow_up_date')
            ->columns([
                TextColumn::make('contact.company.name')
                    ->label('Company')
                    ->placeholder('—'),
                TextColumn::make('contact.name')
                    ->label('Contact'),
                TextColumn::make('assignedAgent.name')
                    ->label('Agent')
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('follow_up_date')
                    ->label('Due')
                    ->date()
                    ->color(fn (Lead $record): string => $record->follow_up_date->isToday() ? 'warning' : 'danger'),
                IconColumn::make('overdue')
                    ->label('Overdue')
                    ->boolean()
                    ->state(fn (Lead $record): bool => $record->isOverdue()),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Open')
                    ->url(fn (Lead $record): string => LeadResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([5, 10, 25]);
    }
}
