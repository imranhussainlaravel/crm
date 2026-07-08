<?php

namespace App\Filament\Resources\Agents\Pages;

use App\Filament\Resources\Agents\AgentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateAgent extends CreateRecord
{
    protected static string $resource = AgentResource::class;

    protected string $roleToAssign = 'agent';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleToAssign = $data['role'] ?? 'agent';
        unset($data['role']);

        if ($this->roleToAssign !== 'agent') {
            $data['work_scope'] = null;
        }

        $data['password'] = Hash::make($data['password']);
        $data['created_by_admin_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->assignRole($this->roleToAssign);
    }
}
