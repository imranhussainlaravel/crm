<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lead details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('contact.company.name')->label('Company'),
                        TextEntry::make('contact.name')->label('Contact'),
                        TextEntry::make('contact.phone')->label('Phone')->placeholder('—'),
                        TextEntry::make('source')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('product_interest')->label('Product interest'),
                        TextEntry::make('assignedAgent.name')->label('Assigned agent')->placeholder('Unassigned'),
                        TextEntry::make('follow_up_date')->label('Follow-up date')->date()->placeholder('—'),
                        TextEntry::make('lost_reason')->label('Lost reason')->placeholder('—'),
                        TextEntry::make('follow_up_note')->label('Follow-up note')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('reassignedByAdmin.name')->label('Last reassigned by')->placeholder('—'),
                        TextEntry::make('reassigned_at')->label('Reassigned at')->dateTime()->placeholder('—'),
                    ]),

                Section::make('Activity timeline')
                    ->schema([
                        RepeatableEntry::make('activities')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('type')->badge()->label(''),
                                TextEntry::make('user.name')->label('')->placeholder('System'),
                                TextEntry::make('note')->label('')->placeholder('—')->columnSpanFull(),
                                TextEntry::make('created_at')->label('')->since(),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}
