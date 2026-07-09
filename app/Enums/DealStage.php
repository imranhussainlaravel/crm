<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DealStage: string implements HasColor, HasLabel
{
    case Quoted = 'quoted';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    public function getLabel(): string
    {
        return match ($this) {
            self::Quoted => 'Quoted',
            self::Negotiation => 'Negotiation',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Quoted => 'warning',
            self::Negotiation => 'warning',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }

    /** @return list<self> */
    public static function pipelineOrder(): array
    {
        return [self::Quoted, self::Negotiation, self::Won, self::Lost];
    }
}
