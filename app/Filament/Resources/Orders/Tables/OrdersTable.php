<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('deal.lead.contact.company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deal.lead.contact.name')
                    ->label('Contact'),
                TextColumn::make('deal.lead.product_interest')
                    ->label('Product'),
                TextColumn::make('deal.salesRep.name')
                    ->label('Agent')
                    ->placeholder('Unassigned'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('deadline')
                    ->date()
                    ->sortable(),
                TextColumn::make('dispatch.dispatch_date')
                    ->label('Dispatched on')
                    ->date()
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('startProduction')
                    ->label('Start production')
                    ->icon('heroicon-o-play')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Pending
                        && (auth()->user()?->isProduction() || auth()->user()?->isAdmin()))
                    ->action(function (Order $record): void {
                        self::transition($record, OrderStatus::InProduction);
                    }),
                Action::make('markReady')
                    ->label('Mark ready to dispatch')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::InProduction
                        && (auth()->user()?->isProduction() || auth()->user()?->isAdmin()))
                    ->action(function (Order $record): void {
                        self::transition($record, OrderStatus::ReadyToDispatch);
                    }),
                Action::make('dispatch')
                    ->label('Dispatch')
                    ->icon('heroicon-o-truck')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::ReadyToDispatch
                        && (auth()->user()?->isProduction() || auth()->user()?->isAdmin()))
                    ->schema([
                        TextInput::make('vehicle_info')
                            ->label('Vehicle / driver info')
                            ->required(),
                        DatePicker::make('dispatch_date')
                            ->label('Dispatch date')
                            ->default(now())
                            ->required(),
                        TextInput::make('delivery_address')
                            ->required(),
                        TextInput::make('invoice_no')
                            ->label('Invoice / tracking number')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->dispatch()->updateOrCreate(['order_id' => $record->id], $data);
                        self::transition($record, OrderStatus::Dispatched);
                    }),
                Action::make('markDelivered')
                    ->label('Mark delivered')
                    ->icon('heroicon-o-home')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Dispatched
                        && (auth()->user()?->isProduction() || auth()->user()?->isAdmin()))
                    ->action(function (Order $record): void {
                        self::transition($record, OrderStatus::Delivered);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function transition(Order $record, OrderStatus $status): void
    {
        $record->update(['status' => $status]);

        Notification::make()
            ->title("Order status changed to {$status->getLabel()}")
            ->body($record->deal?->lead?->contact?->company?->name)
            ->success()
            ->send();

        if ($agent = $record->deal?->salesRep) {
            Notification::make()
                ->title("Order for {$record->deal?->lead?->contact?->company?->name} is now {$status->getLabel()}")
                ->success()
                ->sendToDatabase($agent);
        }
    }
}
