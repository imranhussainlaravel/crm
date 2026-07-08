<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LeadActivityType: string implements HasIcon, HasLabel
{
    case Call = 'call';
    case Note = 'note';
    case Email = 'email';
    case StatusChange = 'status_change';
    case Reassignment = 'reassignment';

    public function getLabel(): string
    {
        return match ($this) {
            self::Call => 'Call logged',
            self::Note => 'Note',
            self::Email => 'Email',
            self::StatusChange => 'Status changed',
            self::Reassignment => 'Reassigned',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Call => 'heroicon-o-phone',
            self::Note => 'heroicon-o-pencil-square',
            self::Email => 'heroicon-o-envelope',
            self::StatusChange => 'heroicon-o-arrow-path',
            self::Reassignment => 'heroicon-o-user-plus',
        };
    }
}
