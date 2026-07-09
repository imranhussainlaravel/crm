<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'deal_id',
    'version',
    'status',
    'total_value',
    'discount_percent',
    'created_by',
    'discount_approved_by',
    'discount_approved_at',
])]
class Quotation extends Model
{
    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'total_value' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_approved_at' => 'datetime',
        ];
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function discountApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discount_approved_by');
    }

    public function recalculateTotals(): void
    {
        $items = $this->items()->with('product')->get();

        $totalValue = $items->sum(fn (QuotationItem $item) => $item->quantity * $item->unit_price);
        $listValue = $items->sum(fn (QuotationItem $item) => $item->quantity * (float) $item->product->base_price);

        $discountPercent = $listValue > 0
            ? max(0, round((($listValue - $totalValue) / $listValue) * 100, 2))
            : 0;

        $this->update([
            'total_value' => $totalValue,
            'discount_percent' => $discountPercent,
        ]);

        if ($this->deal->quotations()->first()?->is($this)) {
            $this->deal->update(['value' => $totalValue]);
        }
    }

    public function needsDiscountApproval(): bool
    {
        $threshold = (float) SystemSetting::get('discount_approval_threshold', 10);

        return (float) $this->discount_percent > $threshold && $this->discount_approved_by === null;
    }
}
