<?php

namespace App\Models;

use App\Enums\LeadActivityType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lead_id', 'user_id', 'type', 'note'])]
class LeadActivity extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'type' => LeadActivityType::class,
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
