<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Enums\DealStage;
use App\Filament\Resources\Deals\DealResource;
use App\Models\Deal;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\SelectFilter;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;

class DealsBoard extends BoardResourcePage
{
    protected static string $resource = DealResource::class;

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
                ->url(fn (): string => DealResource::getUrl('list')),
            CreateAction::make(),
        ];
    }

    public function board(Board $board): Board
    {
        return $board
            ->headerToolbar()
            ->query(fn () => DealResource::getEloquentQuery()->with(['lead.contact.company', 'salesRep']))
            ->columnIdentifier('stage')
            ->positionIdentifier('position')
            ->recordTitleAttribute('id')
            ->columns(
                collect(DealStage::pipelineOrder())
                    ->map(fn (DealStage $stage) => Column::enum($stage))
                    ->all()
            )
            ->cardSchema(fn (Schema $schema) => $schema->components([
                TextEntry::make('lead.contact.company.name')
                    ->label('Company')
                    ->weight('bold')
                    ->placeholder('—'),
                TextEntry::make('lead.contact.name')
                    ->label('Contact'),
                TextEntry::make('value')
                    ->money()
                    ->placeholder('—'),
                TextEntry::make('probability')
                    ->suffix('%')
                    ->placeholder('—')
                    ->icon('heroicon-o-chart-bar'),
                TextEntry::make('expected_close_date')
                    ->label('Expected close')
                    ->date()
                    ->placeholder('—')
                    ->icon('heroicon-o-calendar'),
                TextEntry::make('salesRep.name')
                    ->label('Sales rep')
                    ->placeholder('Unassigned')
                    ->icon('heroicon-o-user'),
            ]))
            ->recordActions([
                EditAction::make()
                    ->url(fn (Deal $record): string => DealResource::getUrl('view', ['record' => $record])),
                DealResource::markWonAction(),
                DealResource::markLostAction(),
            ])
            ->filters([
                SelectFilter::make('sales_rep_id')
                    ->relationship('salesRep', 'name')
                    ->label('Agent'),
            ])
            ->searchable(['lead.contact.name']);
    }

    public function moveCard(
        string $cardId,
        string $targetColumnId,
        ?string $afterCardId = null,
        ?string $beforeCardId = null
    ): void {
        $targetStage = DealStage::from($targetColumnId);

        if (in_array($targetStage, [DealStage::Won, DealStage::Lost], true)) {
            Notification::make()
                ->title('Use the "Mark as Won" / "Mark as Lost" action to record the outcome.')
                ->warning()
                ->send();
            $this->dispatch('$refresh');

            return;
        }

        parent::moveCard($cardId, $targetColumnId, $afterCardId, $beforeCardId);
    }
}
