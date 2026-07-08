<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Enums\LeadActivityType;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['assigned_agent_id']) && auth()->user()?->isAgent()) {
            $data['assigned_agent_id'] = auth()->id();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->activities()->create([
            'user_id' => auth()->id(),
            'type' => LeadActivityType::Note,
            'note' => 'Lead created.',
        ]);
    }
}
