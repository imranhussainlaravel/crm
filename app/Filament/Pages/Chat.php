<?php

namespace App\Filament\Pages;

use App\Enums\UserStatus;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Chat extends Page
{
    protected string $view = 'filament.pages.chat';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Chat';

    protected static ?int $navigationSort = -1;

    public ?string $activeType = null;

    public ?string $activeId = null;

    public ?string $activeChannel = null;

    /** @var array<int, array<string, mixed>> */
    public array $chatMessages = [];

    public string $newMessageBody = '';

    /** @var array<string, string> */
    public static array $groups = [
        'sales-team' => 'Sales Team',
        'production-updates' => 'Production Updates',
    ];

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    /**
     * @return Collection<int, User>
     */
    public function getContacts(): Collection
    {
        return User::query()
            ->where('status', UserStatus::Active)
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();
    }

    public function unreadCountFrom(int $userId): int
    {
        return Message::where('sender_id', $userId)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    public function selectDm(int $userId): void
    {
        $this->activeType = 'dm';
        $this->activeId = (string) $userId;
        $this->activeChannel = Message::dmBroadcastChannel(auth()->id(), $userId);

        Message::where('sender_id', $userId)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->loadMessages();
    }

    public function selectGroup(string $channel): void
    {
        $this->activeType = 'group';
        $this->activeId = $channel;
        $this->activeChannel = Message::groupBroadcastChannel($channel);

        $this->loadMessages();
    }

    protected function loadMessages(): void
    {
        $query = $this->activeType === 'dm'
            ? Message::query()->between(auth()->id(), (int) $this->activeId)
            : Message::query()->inChannel((string) $this->activeId);

        $this->chatMessages = $query->with('sender')
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message) => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name,
                'content' => $message->content,
                'created_at' => $message->created_at->format('H:i'),
            ])
            ->all();
    }

    public function sendMessage(): void
    {
        $this->validate([
            'newMessageBody' => ['required', 'string', 'max:2000'],
        ]);

        if (! $this->activeType) {
            return;
        }

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->activeType === 'dm' ? (int) $this->activeId : null,
            'channel' => $this->activeType === 'group' ? $this->activeId : null,
            'content' => $this->newMessageBody,
        ]);

        MessageSent::dispatch($message);

        $this->chatMessages[] = [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender_name' => auth()->user()->name,
            'content' => $message->content,
            'created_at' => $message->created_at->format('H:i'),
        ];

        $this->newMessageBody = '';
    }

    public function onMessageReceived(array $event): void
    {
        $existingIds = array_column($this->chatMessages, 'id');

        if (in_array($event['id'], $existingIds, true)) {
            return;
        }

        $this->chatMessages[] = [
            'id' => $event['id'],
            'sender_id' => $event['sender_id'],
            'sender_name' => $event['sender_name'],
            'content' => $event['content'],
            'created_at' => Carbon::parse($event['created_at'])->format('H:i'),
        ];

        if ($this->activeType === 'dm' && $event['sender_id'] === (int) $this->activeId) {
            Message::where('id', $event['id'])->update(['read_at' => now()]);
        }
    }
}
