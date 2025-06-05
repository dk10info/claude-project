<div class="space-y-4 max-h-96 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
    @php
    $assignedUserIds = $getRecord()->assignedUsers->pluck('id')->toArray();
    $currentUserId = auth()->id();
    @endphp

    @forelse ($getRecord()->replies()->with('user')->orderBy('created_at', 'asc')->get() as $reply)
    @php
    $isCurrentUser = $reply->user_id === $currentUserId;
    $isAssignedUser = in_array($reply->user_id, $assignedUserIds);
    @endphp

    <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
        <div class="max-w-[70%] {{ $isCurrentUser ? 'order-2' : 'order-1' }}">
            <div class="flex items-end gap-2 {{ $isCurrentUser ? 'flex-row-reverse' : 'flex-row' }}">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full {{ $isCurrentUser ? 'bg-primary-500' : 'bg-gray-400' }} flex items-center justify-center">
                        <span class="text-xs text-white font-semibold">
                            {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                        </span>
                    </div>
                </div>

                <!-- Message bubble -->
                <div class="flex flex-col {{ $isCurrentUser ? 'items-end' : 'items-start' }}">
                    <!-- User name and role -->
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1 px-1">
                        {{ $reply->user->name }}
                        @if($reply->user->hasRole('admin'))
                        <span class="text-danger-600 dark:text-danger-400">(Admin)</span>
                        @elseif($isAssignedUser)
                        <span class="text-primary-600 dark:text-primary-400">(Assigned)</span>
                        @endif
                    </div>

                    <!-- Message content -->
                    <div class="rounded-xl px-4 py-2 {{ $isCurrentUser ? 'bg-primary-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-200 dark:border-gray-700' }}">
                        <p class="text-sm whitespace-pre-wrap">{{ $reply->content }}</p>
                    </div>

                    <!-- Timestamp -->
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1 px-1">
                        {{ $reply->created_at->format('g:i A') }}
                        @if($reply->created_at->isToday())
                        Today
                        @elseif($reply->created_at->isYesterday())
                        Yesterday
                        @else
                        {{ $reply->created_at->format('M d, Y') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-8">
        <svg class="mx-auto h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <p class="mt-2">No replies yet. Start the conversation!</p>
    </div>
    @endforelse
</div>

@if (isset($canAddReply) && is_bool($canAddReply) && $canAddReply)
<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
    <form wire:submit.prevent="addReply" class="space-y-4">
        <div class="fi-fo-field-wrp">
            <div class="fi-input-wrp">
                <textarea
                    wire:model.defer="data.reply_content"
                    rows="3"
                    required
                    placeholder="Type your reply here..."
                    class="fi-textarea block w-full rounded-lg border-gray-300 px-3 py-1.5 text-base text-gray-950 shadow-sm outline-none transition duration-75 placeholder:text-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:text-gray-400 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:text-gray-500 sm:text-sm sm:leading-6"></textarea>
            </div>
        </div>

        <div class="flex justify-end">
            <x-filament::button type="submit" size="sm">
                Add Reply
            </x-filament::button>
        </div>
    </form>
</div>
@endif

@script
<script>
    $wire.on('reply-added', () => {
        setTimeout(() => {
            $wire.dispatch('$refresh');
        }, 100);
    });
</script>
@endscript

<style>
    /* Custom scrollbar for the chat container */
    .max-h-96::-webkit-scrollbar {
        width: 6px;
    }

    .max-h-96::-webkit-scrollbar-track {
        background: transparent;
    }

    .max-h-96::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.5);
        border-radius: 3px;
    }

    .dark .max-h-96::-webkit-scrollbar-thumb {
        background-color: rgba(75, 85, 99, 0.5);
    }
</style>