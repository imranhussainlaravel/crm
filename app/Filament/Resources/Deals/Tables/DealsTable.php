<?php

namespace App\Filament\Resources\Deals\Tables;

use App\Enums\DealStage;
use App\Enums\ProductInterest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.contact.company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lead.contact.name')
                    ->label('Contact'),
                TextColumn::make('lead.product_interest')
                    ->label('Product'),
                TextColumn::make('salesRep.name')
                    ->label('Sales rep')
                    ->searchable(),
                TextColumn::make('stage')
                    ->badge(),
                TextColumn::make('value')
                    ->money()
                    ->sortable(),
                TextColumn::make('expected_close_date')
                    ->label('Expected close')
                    ->date()
                    ->sortable(),
                TextColumn::make('probability')
                    ->suffix('%')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('sales_rep_id')
                    ->label('Agent')
                    ->relationship('salesRep', 'name'),
                SelectFilter::make('product_interest')
                    ->label('Product')
                    ->options(ProductInterest::class)
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $value) => $query->whereHas('lead', fn (Builder $q) => $q->where('product_interest', $value))
                    )),
                SelectFilter::make('stage')
                    ->options(DealStage::class),
                Filter::make('value_range')
                    ->schema([
                        TextInput::make('value_from')->numeric()->label('Value from'),
                        TextInput::make('value_to')->numeric()->label('Value to'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['value_from'] ?? null, fn (Builder $q, $v) => $q->where('value', '>=', $v))
                        ->when($data['value_to'] ?? null, fn (Builder $q, $v) => $q->where('value', '<=', $v))),
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
