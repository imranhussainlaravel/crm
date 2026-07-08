<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductInterest: string implements HasLabel
{
    case Boxes = 'boxes';
    case Pouches = 'pouches';
    case Labels = 'labels';
    case Cartons = 'cartons';
    case Custom = 'custom';

    public function getLabel(): string
    {
        return match ($this) {
            self::Boxes => 'Corrugated Boxes',
            self::Pouches => 'Pouches',
            self::Labels => 'Labels',
            self::Cartons => 'Cartons',
            self::Custom => 'Custom Packaging',
        };
    }
}
