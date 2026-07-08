<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\ProductInterest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contact.company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact.name')
                    ->label('Contact')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('product_interest')
                    ->label('Product'),
                TextColumn::make('source'),
                TextColumn::make('assignedAgent.name')
                    ->label('Agent')
                    ->placeholder('Unassigned'),
                TextColumn::make('follow_up_date')
                    ->label('Follow-up')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null),
                IconColumn::make('is_overdue')
                    ->label('Overdue')
                    ->boolean()
                    ->state(fn ($record) => $record->isOverdue()),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(LeadStatus::class),
                SelectFilter::make('source')
                    ->options(LeadSource::class),
                SelectFilter::make('product_interest')
                    ->label('Product')
                    ->options(ProductInterest::class),
                SelectFilter::make('assigned_agent_id')
                    ->label('Agent')
                    ->relationship('assignedAgent', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
