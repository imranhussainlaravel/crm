<x-filament-panels::page>
    <div wire:poll.10s class="flex gap-6" style="min-height: 60vh;">
        {{-- Sidebar --}}
        <div class="w-64 flex-shrink-0 space-y-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 mb-2">Direct Messages</h3>
                <ul class="space-y-1">
                    @foreach ($this->getContacts() as $contact)
                        <li>
                            <button
                                type="button"
                                wire:click="selectDm({{ $contact->id }})"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm text-left transition
                                    {{ $activeType === 'dm' && $activeId === (string) $contact->id ? 'bg-teal-50 text-teal-700 font-medium' : 'hover:bg-gray-50 text-gray-700' }}"
                            >
                                <span class="flex items-center gap-2">
                                    <span
                                        class="inline-block w-2 h-2 rounded-full {{ $contact->isOnline() ? 'bg-green-500' : 'bg-gray-300' }}"
                                        title="{{ $contact->isOnline() ? 'Online' : 'Offline' }}"
                                    ></span>
                                    {{ $contact->name }}
                                </span>
                                @if ($this->unreadCountFrom($contact->id) > 0)
                                    <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-xs font-semibold text-white bg-teal-600 rounded-full">
                                        {{ $this->unreadCountFrom($contact->id) }}
                                    </span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-500 mb-2">Group Channels</h3>
                <ul class="space-y-1">
                    @foreach (self::$groups as $key => $label)
                        <li>
                            <button
                                type="button"
                                wire:click="selectGroup('{{ $key }}')"
                                class="w-full flex items-center px-3 py-2 rounded-lg text-sm text-left transition
                                    {{ $activeType === 'group' && $activeId === $key ? 'bg-teal-50 text-teal-700 font-medium' : 'hover:bg-gray-50 text-gray-700' }}"
                            >
                                # {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Conversation --}}
        <div class="flex-1 flex flex-col rounded-xl border border-gray-200 bg-white overflow-hidden">
            @if (! $activeType)
                <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">
                    Select a conversation to start chatting.
                </div>
            @else
                <div class="flex-1 overflow-y-auto p-4 space-y-3" style="max-height: 55vh;">
                    @forelse ($chatMessages as $message)
                        <div class="flex flex-col {{ $message['sender_id'] === auth()->id() ? 'items-end' : 'items-start' }}">
                            <div
                                class="max-w-md px-3 py-2 rounded-lg text-sm
                                    {{ $message['sender_id'] === auth()->id() ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-800' }}"
                            >
                                @if ($message['sender_id'] !== auth()->id())
                                    <div class="text-xs font-semibold opacity-70 mb-0.5">{{ $message['sender_name'] }}</div>
                                @endif
                                <div>{{ $message['content'] }}</div>
                            </div>
                            <span class="text-[11px] text-gray-400 mt-0.5">{{ $message['created_at'] }}</span>
                        </div>
                    @empty
                        <div class="text-center text-gray-400 text-sm py-8">No messages yet — say hello.</div>
                    @endforelse
                </div>

                <form wire:submit="sendMessage" class="flex items-center gap-2 border-t border-gray-200 p-3">
                    <input
                        type="text"
                        wire:model="newMessageBody"
                        placeholder="Type a message..."
                        class="fi-input flex-1 rounded-lg border-gray-300 text-sm px-3 py-2"
                        autocomplete="off"
                    />
                    <x-filament::button type="submit" size="sm">
                        Send
                    </x-filament::button>
                </form>
            @endif
        </div>
    </div>

    @script
    <script>
        let currentChannel = null;

        function unsubscribe() {
            if (currentChannel) {
                window.Echo.leave(currentChannel);
                currentChannel = null;
            }
        }

        function subscribeTo(channel) {
            unsubscribe();

            if (! channel) {
                return;
            }

            currentChannel = channel;

            window.Echo.private(channel).listen('.message.sent', (event) => {
                $wire.onMessageReceived(event);
            });
        }

        subscribeTo($wire.activeChannel);

        $wire.$watch('activeChannel', (value) => subscribeTo(value));
    </script>
    @endscript
</x-filament-panels::page>
