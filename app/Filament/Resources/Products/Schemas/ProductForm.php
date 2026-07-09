<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductInterest;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options(ProductInterest::class)
                    ->required(),
                TextInput::make('material'),
                TextInput::make('size_options')
                    ->helperText('e.g. "6x4x4in, 10x8x6in, custom"'),
                TextInput::make('moq')
                    ->label('MOQ')
                    ->helperText('Minimum order quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('base_price')
                    ->label('Base price (per unit)')
                    ->required()
                    ->numeric(),
                Repeater::make('priceTiers')
                    ->relationship()
                    ->label('Price tiers by quantity')
                    ->schema([
                        TextInput::make('min_quantity')
                            ->label('Min. quantity')
                            ->required()
                            ->numeric(),
                        TextInput::make('unit_price')
                            ->label('Unit price')
                            ->required()
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->addActionLabel('Add price tier')
                    ->defaultItems(0),
            ]);
    }
}
