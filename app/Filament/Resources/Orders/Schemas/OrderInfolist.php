<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('deal.lead.contact.company.name')->label('Company'),
                        TextEntry::make('deal.lead.contact.name')->label('Contact'),
                        TextEntry::make('deal.lead.product_interest')->label('Product'),
                        TextEntry::make('deal.salesRep.name')->label('Agent')->placeholder('Unassigned'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('deadline')->date()->placeholder('—'),
                        TextEntry::make('special_instructions')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Dispatch details')
                    ->visible(fn ($record) => $record->dispatch !== null)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('dispatch.vehicle_info')->label('Vehicle / driver'),
                        TextEntry::make('dispatch.dispatch_date')->label('Dispatch date')->date(),
                        TextEntry::make('dispatch.delivery_address')->label('Delivery address'),
                        TextEntry::make('dispatch.invoice_no')->label('Invoice / tracking no.'),
                    ]),
            ]);
    }
}
