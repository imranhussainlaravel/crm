<?php

namespace App\Filament\Resources\Deals\Schemas;

use App\Enums\DealStage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lead_id')
                    ->label('Lead')
                    ->relationship('lead', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->contact?->company?->name} — {$record->contact?->name}")
                    ->searchable(['id'])
                    ->required(),
                Select::make('sales_rep_id')
                    ->label('Sales rep')
                    ->relationship('salesRep', 'name', fn ($query) => $query->role('agent'))
                    ->searchable()
                    ->preload(),
                Select::make('stage')
                    ->options(DealStage::class)
                    ->default('quoted')
                    ->required(),
                TextInput::make('value')
                    ->numeric(),
                DatePicker::make('expected_close_date')
                    ->label('Expected close date'),
                TextInput::make('probability')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
            ]);
    }
}
