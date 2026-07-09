<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sender_id', 'receiver_id', 'channel', 'content', 'read_at'])]
class Message extends Model
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopeBetween(Builder $query, int $userAId, int $userBId): Builder
    {
        return $query->whereNull('channel')
            ->where(function (Builder $q) use ($userAId, $userBId) {
                $q->where(['sender_id' => $userAId, 'receiver_id' => $userBId])
                    ->orWhere(['sender_id' => $userBId, 'receiver_id' => $userAId]);
            });
    }

    public function scopeInChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    public static function dmBroadcastChannel(int $userAId, int $userBId): string
    {
        $ids = [$userAId, $userBId];
        sort($ids);

        return 'chat.dm.' . implode('.', $ids);
    }

    public static function groupBroadcastChannel(string $channel): string
    {
        return 'chat.group.' . $channel;
    }
}
