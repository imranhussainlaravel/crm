<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case InProduction = 'in_production';
    case ReadyToDispatch = 'ready_to_dispatch';
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProduction => 'In Production',
            self::ReadyToDispatch => 'Ready to Dispatch',
            self::Dispatched => 'Dispatched',
            self::Delivered => 'Delivered',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::InProduction => 'warning',
            self::ReadyToDispatch => 'info',
            self::Dispatched => 'primary',
            self::Delivered => 'success',
        };
    }

    /** @return list<self> */
    public static function pipelineOrder(): array
    {
        return [self::Pending, self::InProduction, self::ReadyToDispatch, self::Dispatched, self::Delivered];
    }
}
