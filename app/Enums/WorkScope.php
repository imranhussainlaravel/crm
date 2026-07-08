<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WorkScope: string implements HasLabel
{
    case LeadGenOnly = 'lead_gen_only';
    case SalesOnly = 'sales_only';
    case FullCycle = 'full_cycle';

    public function getLabel(): string
    {
        return match ($this) {
            self::LeadGenOnly => 'Lead Gen only',
            self::SalesOnly => 'Sales only (closer)',
            self::FullCycle => 'Full Cycle',
        };
    }

    public function canCreateLeads(): bool
    {
        return $this !== self::SalesOnly;
    }

    public function canAdvancePastQualified(): bool
    {
        return $this !== self::LeadGenOnly;
    }
}
