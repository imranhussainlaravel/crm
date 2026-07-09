<?php

namespace App\Filament\Resources\Deals;

use App\Enums\DealStage;
use App\Enums\LostReason;
use App\Enums\OrderStatus;
use App\Filament\Resources\Deals\Pages\CreateDeal;
use App\Filament\Resources\Deals\Pages\DealsBoard;
use App\Filament\Resources\Deals\Pages\EditDeal;
use App\Filament\Resources\Deals\Pages\ListDeals;
use App\Filament\Resources\Deals\Pages\ViewDeal;
use App\Filament\Resources\Deals\RelationManagers\QuotationsRelationManager;
use App\Filament\Resources\Deals\Schemas\DealForm;
use App\Filament\Resources\Deals\Schemas\DealInfolist;
use App\Filament\Resources\Deals\Tables\DealsTable;
use App\Models\Deal;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DealResource extends Resource
{
    protected static ?string $model = Deal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales Pipeline';

    protected static ?string $navigationLabel = 'Deals';

    public static function form(Schema $schema): Schema
    {
        return DealForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DealInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DealsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isAdmin()) {
            return $query;
        }

        return $query->where('sales_rep_id', $user?->id);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return ($user?->isAdmin() || $user?->isAgent()) ?? false;
    }

    public static function markWonAction(): Action
    {
        return Action::make('markWon')
            ->label('Mark as Won')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Deal $record): bool => $record->stage !== DealStage::Won)
            ->requiresConfirmation()
            ->modalDescription('This creates an order and marks the lead as Won.')
            ->action(function (Deal $record): void {
                $record->update(['stage' => DealStage::Won]);
                $record->lead?->update(['status' => 'won']);

                Order::firstOrCreate(
                    ['deal_id' => $record->id],
                    ['status' => OrderStatus::Pending]
                );

                Notification::make()->title('Deal won — order created')->success()->send();
            });
    }

    public static function markLostAction(): Action
    {
        return Action::make('markLost')
            ->label('Mark as Lost')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Deal $record): bool => $record->stage !== DealStage::Lost)
            ->schema([
                Select::make('lost_reason')
                    ->label('Reason')
                    ->options(LostReason::class)
                    ->required(),
            ])
            ->action(function (Deal $record, array $data): void {
                $record->update([
                    'stage' => DealStage::Lost,
                    'lost_reason' => $data['lost_reason'],
                ]);
                $record->lead?->update([
                    'status' => 'lost',
                    'lost_reason' => $data['lost_reason'],
                ]);

                Notification::make()->title('Deal marked as lost')->success()->send();
            });
    }

    public static function getRelations(): array
    {
        return [
            QuotationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => DealsBoard::route('/'),
            'list' => ListDeals::route('/list'),
            'create' => CreateDeal::route('/create'),
            'view' => ViewDeal::route('/{record}'),
            'edit' => EditDeal::route('/{record}/edit'),
        ];
    }
}
