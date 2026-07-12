<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Enums\DealStage;
use App\Enums\LeadActivityType;
use App\Enums\LeadStatus;
use App\Enums\LostReason;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\SelectFilter;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;

class LeadsBoard extends BoardResourcePage
{
    protected static string $resource = LeadResource::class;

    public function getPageClasses(): array
    {
        return ['flowforge-board-page'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tableView')
                ->label('Table view')
                ->icon('heroicon-o-table-cells')
                ->url(fn (): string => LeadResource::getUrl('list')),
            CreateAction::make(),
        ];
    }

    public function board(Board $board): Board
    {
        return $board
            ->headerToolbar()
            ->query(fn () => LeadResource::getEloquentQuery()->with(['contact.company', 'assignedAgent']))
            ->columnIdentifier('status')
            ->positionIdentifier('position')
            ->recordTitleAttribute('id')
            ->columns(
                collect(LeadStatus::pipelineOrder())
                    ->map(fn (LeadStatus $status) => Column::enum($status))
                    ->all()
            )
            ->cardSchema(fn (Schema $schema) => $schema->components([
                TextEntry::make('contact.company.name')
                    ->label('Company')
                    ->weight('bold')
                    ->placeholder('—'),
                TextEntry::make('contact.name')
                    ->label('Contact'),
                TextEntry::make('product_interest'),
                TextEntry::make('assignedAgent.name')
                    ->label('Agent')
                    ->placeholder('Unassigned')
                    ->icon('heroicon-o-user'),
                TextEntry::make('follow_up_date')
                    ->label('Follow-up')
                    ->date()
                    ->placeholder('—')
                    ->icon('heroicon-o-calendar')
                    ->color(fn (?Lead $record) => $record?->isOverdue() ? 'danger' : 'gray'),
            ]))
            ->recordActions([
                EditAction::make()
                    ->url(fn (Lead $record): string => LeadResource::getUrl('view', ['record' => $record])),
                LeadResource::markLostAction(),
                LeadResource::reassignAction(),
            ])
            ->columnActions([
                Action::make('addLead')
                    ->label('Add lead')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => LeadResource::getUrl('create'))
                    ->visible(fn (): bool => auth()->user()?->can('create', Lead::class) ?? false),
            ])
            ->filters([
                SelectFilter::make('assigned_agent_id')
                    ->relationship('assignedAgent', 'name')
                    ->label('Agent'),
            ])
            ->searchable(['contact.name']);
    }

    public function moveCard(
        string $cardId,
        string $targetColumnId,
        ?string $afterCardId = null,
        ?string $beforeCardId = null
    ): void {
        /** @var Lead $lead */
        $lead = Lead::query()->findOrFail($cardId);
        $targetStatus = LeadStatus::from($targetColumnId);
        /** @var User $user */
        $user = auth()->user();

        if ($targetStatus === LeadStatus::Lost) {
            Notification::make()
                ->title('Use the "Mark as Lost" action to record a reason.')
                ->warning()
                ->send();
            $this->dispatch('$refresh');

            return;
        }

        if (
            $user->isAgent()
            && $targetStatus->isPastQualified()
            && ! ($user->work_scope?->canAdvancePastQualified() ?? false)
        ) {
            Notification::make()
                ->title('Lead Gen agents cannot move leads past Qualified — hand off to Sales.')
                ->danger()
                ->send();
            $this->dispatch('$refresh');

            return;
        }

        $previousStatus = $lead->status;

        parent::moveCard($cardId, $targetColumnId, $afterCardId, $beforeCardId);

        if ($previousStatus !== $targetStatus) {
            $lead->activities()->create([
                'user_id' => $user->id,
                'type' => LeadActivityType::StatusChange,
                'note' => "Status changed from {$previousStatus->getLabel()} to {$targetStatus->getLabel()}",
            ]);
        }

        if ($targetStatus === LeadStatus::Quoted) {
            Deal::firstOrCreate(
                ['lead_id' => $lead->id],
                ['sales_rep_id' => $lead->assigned_agent_id, 'stage' => DealStage::Quoted]
            );
        }
    }
}
