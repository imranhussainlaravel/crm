<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('material')
                    ->placeholder('-'),
                TextEntry::make('size_options')
                    ->placeholder('-'),
                TextEntry::make('moq')
                    ->numeric(),
                TextEntry::make('base_price')
                    ->money(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
