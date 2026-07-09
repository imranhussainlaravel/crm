<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Filament\Resources\Deals\DealResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeals extends ListRecords
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('boardView')
                ->label('Board view')
                ->icon('heroicon-o-view-columns')
                ->url(fn (): string => DealResource::getUrl('index')),
            CreateAction::make(),
        ];
    }
}
