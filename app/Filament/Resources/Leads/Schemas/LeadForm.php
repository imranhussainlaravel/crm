<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\ProductInterest;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('contact_id')
                    ->label('Contact')
                    ->relationship('contact', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->company?->name})")
                    ->searchable(['name'])
                    ->preload()
                    ->createOptionForm([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('city'),
                            ])
                            ->required(),
                        TextInput::make('name')->label('Contact person')->required(),
                        TextInput::make('phone'),
                        TextInput::make('email')->email(),
                        TextInput::make('designation'),
                    ])
                    ->required(),
                Select::make('source')
                    ->options(LeadSource::class)
                    ->required(),
                Select::make('product_interest')
                    ->label('Product interest')
                    ->options(ProductInterest::class)
                    ->required(),
                Select::make('status')
                    ->options(LeadStatus::class)
                    ->default(LeadStatus::New->value)
                    ->required(),
                Select::make('assigned_agent_id')
                    ->label('Assigned agent')
                    ->relationship('assignedAgent', 'name', fn ($query) => $query->role('agent'))
                    ->searchable()
                    ->preload(),
                DatePicker::make('follow_up_date')
                    ->label('Follow-up date'),
                Textarea::make('follow_up_note')
                    ->label('Follow-up note')
                    ->columnSpanFull(),
            ]);
    }
}
