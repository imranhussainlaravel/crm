<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Enums\LeadActivityType;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('logCall')
                ->label('Log a call')
                ->icon('heroicon-o-phone')
                ->schema([
                    Textarea::make('note')->label('Notes')->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->activities()->create([
                        'user_id' => auth()->id(),
                        'type' => LeadActivityType::Call,
                        'note' => $data['note'],
                    ]);

                    Notification::make()->title('Call logged')->success()->send();
                }),
            Action::make('addNote')
                ->label('Add note')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    Textarea::make('note')->label('Note')->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->activities()->create([
                        'user_id' => auth()->id(),
                        'type' => LeadActivityType::Note,
                        'note' => $data['note'],
                    ]);

                    Notification::make()->title('Note added')->success()->send();
                }),
            Action::make('setFollowUp')
                ->label('Set follow-up')
                ->icon('heroicon-o-calendar')
                ->schema([
                    DatePicker::make('follow_up_date')->required(),
                    Textarea::make('follow_up_note')->label('Note'),
                ])
                ->fillForm(fn (): array => [
                    'follow_up_date' => $this->record->follow_up_date,
                    'follow_up_note' => $this->record->follow_up_note,
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'follow_up_date' => $data['follow_up_date'],
                        'follow_up_note' => $data['follow_up_note'],
                    ]);

                    $this->record->activities()->create([
                        'user_id' => auth()->id(),
                        'type' => LeadActivityType::Note,
                        'note' => "Follow-up set for {$data['follow_up_date']}",
                    ]);

                    Notification::make()->title('Follow-up set')->success()->send();
                }),
            LeadResource::markLostAction(),
            LeadResource::reassignAction(),
            EditAction::make(),
        ];
    }
}
