<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'vehicle_info', 'dispatch_date', 'delivery_address', 'invoice_no'])]
class Dispatch extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'dispatch_date' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function activityLogLabel(): string
    {
        return "Dispatch for Order #{$this->order_id}";
    }
}
