<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn (Model $model) => $model->recordActivity('created'));
        static::updated(fn (Model $model) => $model->recordActivity('updated'));
        static::deleted(fn (Model $model) => $model->recordActivity('deleted'));
    }

    protected function recordActivity(string $event): void
    {
        if (! auth()->check()) {
            return;
        }

        $changedFields = array_diff(array_keys($this->getChanges()), ['updated_at', 'created_at', 'last_active_at']);

        if ($event === 'updated' && $changedFields === []) {
            return;
        }

        $description = match ($event) {
            'created' => "Created {$this->activityLogLabel()}",
            'updated' => "Updated {$this->activityLogLabel()}".($changedFields ? ' ('.implode(', ', $changedFields).')' : ''),
            'deleted' => "Deleted {$this->activityLogLabel()}",
        };

        ActivityLog::create([
            'causer_id' => auth()->id(),
            'subject_type' => static::class,
            'subject_id' => $this->getKey(),
            'event' => $event,
            'description' => $description,
        ]);
    }

    public function activityLogLabel(): string
    {
        $name = $this->name ?? null;

        return $name
            ? class_basename($this).": {$name}"
            : class_basename($this)." #{$this->getKey()}";
    }
}
