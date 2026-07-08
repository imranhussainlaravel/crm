<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('boardView')
                ->label('Board view')
                ->icon('heroicon-o-view-columns')
                ->url(fn (): string => LeadResource::getUrl('index')),
            CreateAction::make(),
        ];
    }
}
