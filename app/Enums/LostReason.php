<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LostReason: string implements HasLabel
{
    case PriceTooHigh = 'price_too_high';
    case Competitor = 'competitor';
    case NoBudget = 'no_budget';
    case NotInterested = 'not_interested';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::PriceTooHigh => 'Price too high',
            self::Competitor => 'Went with competitor',
            self::NoBudget => 'No budget',
            self::NotInterested => 'Not interested',
            self::Other => 'Other',
        };
    }
}
