<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Enums\DealStage;
use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Deal;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->status === LeadStatus::Quoted) {
            Deal::firstOrCreate(
                ['lead_id' => $this->record->id],
                ['sales_rep_id' => $this->record->assigned_agent_id, 'stage' => DealStage::Quoted]
            );
        }
    }
}
