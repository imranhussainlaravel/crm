<?php

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\LostReason;
use App\Enums\ProductInterest;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'contact_id',
    'source',
    'status',
    'product_interest',
    'assigned_agent_id',
    'reassigned_by_admin_id',
    'reassigned_at',
    'lost_reason',
    'follow_up_date',
    'follow_up_note',
])]
class Lead extends Model
{
    protected function casts(): array
    {
        return [
            'source' => LeadSource::class,
            'status' => LeadStatus::class,
            'product_interest' => ProductInterest::class,
            'lost_reason' => LostReason::class,
            'reassigned_at' => 'datetime',
            'follow_up_date' => 'date',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function reassignedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reassigned_by_admin_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function deal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Deal::class);
    }

    public function isOverdue(): bool
    {
        return $this->follow_up_date !== null && $this->follow_up_date->isPast();
    }
}
