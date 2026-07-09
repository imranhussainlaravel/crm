<?php

namespace App\Filament\Resources\Deals\RelationManagers;

use App\Enums\QuotationStatus;
use App\Models\Product;
use App\Models\Quotation;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class QuotationsRelationManager extends RelationManager
{
    protected static string $relationship = 'quotations';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('items')
                    ->relationship()
                    ->label('Line items')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                if (! $state) {
                                    return;
                                }
                                $product = Product::find($state);
                                $quantity = (int) ($get('quantity') ?: $product?->moq ?? 1);
                                $set('quantity', $quantity);
                                $set('unit_price', $product?->unitPriceForQuantity($quantity));
                            }),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                                $product = Product::find($get('product_id'));
                                if ($product && $state) {
                                    $set('unit_price', $product->unitPriceForQuantity((int) $state));
                                }
                            }),
                        TextInput::make('unit_price')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->addActionLabel('Add line item')
                    ->minItems(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version')
            ->columns([
                TextColumn::make('version')
                    ->label('v')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('total_value')
                    ->label('Total')
                    ->money()
                    ->sortable(),
                TextColumn::make('discount_percent')
                    ->label('Discount')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('createdBy.name')
                    ->label('Created by')
                    ->placeholder('—'),
                TextColumn::make('discountApprovedBy.name')
                    ->label('Discount approved by')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('version', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $latestVersion = $this->getOwnerRecord()->quotations()->max('version') ?? 0;
                        $data['version'] = $latestVersion + 1;
                        $data['created_by'] = auth()->id();

                        return $data;
                    })
                    ->after(function (Quotation $record): void {
                        $record->recalculateTotals();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn (Quotation $record) => $record->recalculateTotals()),
                Action::make('duplicate')
                    ->label('Duplicate as new version')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Quotation $record): void {
                        $latestVersion = $this->getOwnerRecord()->quotations()->max('version') ?? 0;

                        $new = $record->replicate(['status', 'discount_approved_by', 'discount_approved_at']);
                        $new->version = $latestVersion + 1;
                        $new->status = QuotationStatus::Draft;
                        $new->created_by = auth()->id();
                        $new->discount_approved_by = null;
                        $new->discount_approved_at = null;
                        $new->save();

                        foreach ($record->items as $item) {
                            $new->items()->create([
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                            ]);
                        }

                        $new->recalculateTotals();

                        Notification::make()->title("Created version {$new->version}")->success()->send();
                    }),
                Action::make('send')
                    ->label('Mark as Sent')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Quotation $record) => $record->status === QuotationStatus::Draft)
                    ->action(function (Quotation $record): void {
                        if ($record->needsDiscountApproval()) {
                            Notification::make()
                                ->title('This quote\'s discount exceeds the approval threshold — an Admin must approve it first.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update(['status' => QuotationStatus::Sent]);
                        Notification::make()->title('Quotation marked as sent')->success()->send();
                    }),
                Action::make('approveDiscount')
                    ->label('Approve discount')
                    ->icon('heroicon-o-shield-check')
                    ->visible(fn (Quotation $record): bool => (auth()->user()?->isAdmin() ?? false)
                        && $record->needsDiscountApproval())
                    ->action(function (Quotation $record): void {
                        $record->update([
                            'discount_approved_by' => auth()->id(),
                            'discount_approved_at' => now(),
                        ]);
                        Notification::make()->title('Discount approved')->success()->send();
                    }),
                Action::make('clientResponse')
                    ->label('Record client response')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->visible(fn (Quotation $record) => $record->status === QuotationStatus::Sent)
                    ->schema([
                        Select::make('status')
                            ->label('Client response')
                            ->options([
                                QuotationStatus::Approved->value => 'Approved by client',
                                QuotationStatus::Rejected->value => 'Rejected',
                            ])
                            ->required(),
                    ])
                    ->action(fn (Quotation $record, array $data) => $record->update(['status' => $data['status']])),
                Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Quotation $record): string => route('quotations.pdf', $record))
                    ->openUrlInNewTab(),
                Action::make('shareLink')
                    ->label('Share link')
                    ->icon('heroicon-o-link')
                    ->action(function (Quotation $record): void {
                        $url = URL::temporarySignedRoute(
                            'quotations.share',
                            now()->addDays(14),
                            ['quotation' => $record->id]
                        );

                        Notification::make()
                            ->title('Share link (valid 14 days)')
                            ->body($url)
                            ->persistent()
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ]);
    }
}
