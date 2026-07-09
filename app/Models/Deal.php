<?php

namespace App\Models;

use App\Enums\DealStage;
use App\Enums\LostReason;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['lead_id', 'sales_rep_id', 'stage', 'value', 'expected_close_date', 'probability', 'lost_reason'])]
class Deal extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'stage' => DealStage::class,
            'lost_reason' => LostReason::class,
            'expected_close_date' => 'date',
            'value' => 'decimal:2',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class)->latest('version');
    }

    public function activityLogLabel(): string
    {
        return "Deal #{$this->id} ({$this->lead?->contact?->company?->name})";
    }
}
