<?php

namespace App\Filament\Resources\Deals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DealInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Deal details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('lead.contact.company.name')->label('Company'),
                        TextEntry::make('lead.contact.name')->label('Contact'),
                        TextEntry::make('lead.product_interest')->label('Product'),
                        TextEntry::make('salesRep.name')->label('Sales rep')->placeholder('Unassigned'),
                        TextEntry::make('stage')->badge(),
                        TextEntry::make('value')->money()->placeholder('—'),
                        TextEntry::make('expected_close_date')->label('Expected close')->date()->placeholder('—'),
                        TextEntry::make('probability')->suffix('%')->placeholder('—'),
                        TextEntry::make('lost_reason')->label('Lost reason')->placeholder('—'),
                    ]),
            ]);
    }
}
