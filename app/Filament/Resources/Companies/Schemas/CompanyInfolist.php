<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('city')->placeholder('—'),
                        TextEntry::make('industry_notes')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('created_at')->dateTime(),
                    ]),

                Section::make('Contacts')
                    ->schema([
                        RepeatableEntry::make('contacts')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('name')->weight('bold'),
                                TextEntry::make('designation')->placeholder('—'),
                                TextEntry::make('phone')->placeholder('—'),
                                TextEntry::make('email')->placeholder('—'),
                            ])
                            ->columns(4),
                    ])
                    ->collapsible(),

                Section::make('Leads & deal history')
                    ->schema([
                        RepeatableEntry::make('leads')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('contact.name')->label('Contact'),
                                TextEntry::make('status')->badge(),
                                TextEntry::make('product_interest'),
                                TextEntry::make('assignedAgent.name')->label('Agent')->placeholder('Unassigned'),
                                TextEntry::make('created_at')->dateTime()->label('Created'),
                            ])
                            ->columns(5),
                    ])
                    ->collapsible(),
            ]);
    }
}
