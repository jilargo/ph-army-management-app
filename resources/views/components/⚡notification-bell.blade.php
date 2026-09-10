<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    protected $listeners = ['notifications.refresh' => '$refresh'];

    #[Computed]
    public function unreadCount(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    #[Computed]
    public function notifications()
    {
        return auth()->user()->notifications()->latest()->take(12)->get();
    }

    public function openNotification(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->find($notificationId);

        $url = $notification?->data['url'] ?? null;

        $notification?->markAsRead();

        $this->redirect($url ?? route('user-dashboard'));
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }
};

?>

<div x-data="{ open: false }" class="relative" @click.outside="open = false" wire:key="notification-bell">
    {{-- Bell Button --}}
    <button
        type="button"
        @click="open = !open"
        class="relative flex h-10 w-10 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
        aria-label="Notifications">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        @if ($this->unreadCount > 0)
            <span class="absolute top-1 right-1 flex min-w-[18px] h-[18px] items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white px-1">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition
        x-cloak
        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden z-50">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900">Notifications</h3>
            @if ($this->unreadCount > 0)
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                    Mark all as read
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-slate-100">
            @forelse ($this->notifications as $notification)
                @php $data = $notification->data; @endphp
                <button
                    type="button"
                    wire:click="openNotification('{{ $notification->id }}')"
                    class="w-full text-left px-4 py-3 flex gap-3 hover:bg-slate-50 transition {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50/50' }}">
                    <div class="shrink-0 mt-0.5">
                        @if (($data['icon'] ?? '') === 'task')
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </span>
                        @else
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-slate-900">{{ $data['title'] ?? 'Notification' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $data['message'] ?? '' }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if (! $notification->read_at)
                        <span class="shrink-0 mt-1.5 h-2 w-2 rounded-full bg-indigo-500"></span>
                    @endif
                </button>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm text-slate-500">No notifications yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>