<?php

namespace App\Filament\Resources\Agents\Schemas;

use App\Enums\UserStatus;
use App\Enums\WorkScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AgentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.'),
                Select::make('role')
                    ->label('Role')
                    ->options([
                        'agent' => 'Agent',
                        'production' => 'Production / Dispatch',
                    ])
                    ->required()
                    ->live()
                    ->default('agent'),
                Select::make('work_scope')
                    ->label('Work scope')
                    ->options(WorkScope::class)
                    ->visible(fn (Get $get): bool => $get('role') === 'agent')
                    ->required(fn (Get $get): bool => $get('role') === 'agent'),
                Select::make('status')
                    ->label('Status')
                    ->options(UserStatus::class)
                    ->default(UserStatus::Active->value)
                    ->required(),
            ]);
    }
}
