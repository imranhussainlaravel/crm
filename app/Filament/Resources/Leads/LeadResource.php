<?php

namespace App\Filament\Resources\Leads;

use App\Enums\LeadActivityType;
use App\Enums\LeadStatus;
use App\Enums\LostReason;
use App\Filament\Resources\Leads\Pages\CreateLead;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\LeadsBoard;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Filament\Resources\Leads\Schemas\LeadForm;
use App\Filament\Resources\Leads\Schemas\LeadInfolist;
use App\Filament\Resources\Leads\Tables\LeadsTable;
use App\Models\Lead;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales Pipeline';

    public static function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isAdmin()) {
            return $query;
        }

        return $query->where('assigned_agent_id', $user?->id);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ($user?->isAdmin() || $user?->isAgent()) ?? false;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if ($user?->isAdmin()) {
            return true;
        }

        return $user?->isAgent() && ($user->work_scope?->canCreateLeads() ?? false);
    }

    public static function markLostAction(): Action
    {
        return Action::make('markLost')
            ->label('Mark as Lost')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Lead $record): bool => $record->status !== LeadStatus::Lost && (auth()->user()?->can('update', $record) ?? false))
            ->schema([
                Select::make('lost_reason')
                    ->label('Reason')
                    ->options(LostReason::class)
                    ->required(),
            ])
            ->action(function (Lead $record, array $data): void {
                $record->update([
                    'status' => LeadStatus::Lost,
                    'lost_reason' => $data['lost_reason'],
                ]);

                $record->activities()->create([
                    'user_id' => auth()->id(),
                    'type' => LeadActivityType::StatusChange,
                    'note' => 'Marked as Lost: '.LostReason::from($data['lost_reason'])->getLabel(),
                ]);

                Notification::make()->title('Lead marked as lost')->success()->send();
            });
    }

    public static function reassignAction(): Action
    {
        return Action::make('reassign')
            ->label('Reassign')
            ->icon('heroicon-o-user-plus')
            ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false)
            ->schema([
                Select::make('assigned_agent_id')
                    ->label('New agent')
                    ->options(fn (): array => User::role('agent')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),
            ])
            ->action(function (Lead $record, array $data): void {
                $previousAgentName = $record->assignedAgent?->name ?? 'Unassigned';
                $newAgent = User::find($data['assigned_agent_id']);

                $record->update([
                    'assigned_agent_id' => $data['assigned_agent_id'],
                    'reassigned_by_admin_id' => auth()->id(),
                    'reassigned_at' => now(),
                ]);

                $record->activities()->create([
                    'user_id' => auth()->id(),
                    'type' => LeadActivityType::Reassignment,
                    'note' => "Reassigned from {$previousAgentName} to {$newAgent?->name}",
                ]);

                Notification::make()->title('Lead reassigned')->success()->send();
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => LeadsBoard::route('/'),
            'list' => ListLeads::route('/list'),
            'create' => CreateLead::route('/create'),
            'view' => ViewLead::route('/{record}'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }
}
