<?php

use App\Models\Leaves;
use App\Models\Personnel;
use App\Models\Promotions;
use App\Models\Task;
use App\Models\Units;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $heading = 'dashboard';

    #[Computed]
    public function totalPersonnel()
    {
        return Personnel::count();
    }

    #[Computed]
    public function activeSoldiers()
    {
        return Personnel::whereRaw('LOWER(status) = ?', ['active'])->count();
    }

    #[Computed]
    public function inactiveSoldiers()
    {
        return Personnel::whereRaw('LOWER(status) = ?', ['inactive'])->count();
    }

    #[Computed]
    public function retiredSoldiers()
    {
        return Personnel::whereRaw('LOWER(status) = ?', ['retired'])->count();
    }

    #[Computed]
    public function onLeaveSoldiers()
    {
        return Personnel::whereRaw('LOWER(status) = ?', ['leave'])->count();
    }

    #[Computed]
    public function activeUnits()
    {
        return Units::count();
    }

    #[Computed]
    public function pendingLeaves()
    {
        return Leaves::where('status', 'pending')->count();
    }

    #[Computed]
    public function pendingPromotions()
    {
        return Promotions::where('status', 'pending')->count();
    }

    #[Computed]
    public function openTasks()
    {
        return Task::where('status', '!=', 'completed')->count();
    }

    #[Computed]
    public function newlyAcceptedSoldiers()
    {
        return Personnel::where('created_at', '>=', now()->subDays(30))->count();
    }

    #[Computed]
    public function completedTasks()
    {
        return Task::where('status', 'completed')->count();
    }

    #[Computed]
    public function recentPersonnel()
    {
        return Personnel::with('rank', 'units')
            ->latest()
            ->take(6)
            ->get();
    }
};
?>

<div>
    {{-- Dashboard Content --}}

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">
                Total Personnel
            </p>
            <h3 class="text-3xl font-bold mt-2">
                {{ number_format($this->totalPersonnel) }}
            </h3>
            <a href="{{ route('personnel.index') }}" class="text-sm text-indigo-600 mt-2 inline-block hover:underline">
                View personnel &rarr;
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">
                Active Soldiers
            </p>
            <h3 class="text-3xl font-bold mt-2">
                {{ number_format($this->activeSoldiers) }}
            </h3>
            <p class="text-sm text-green-600 mt-2">
                out of {{ number_format($this->totalPersonnel) }} total
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">
                Active Units
            </p>
            <h3 class="text-3xl font-bold mt-2">
                {{ number_format($this->activeUnits) }}
            </h3>
            <p class="text-sm text-gray-500 mt-2">
                Registered units
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">
                Pending Leaves
            </p>
            <h3 class="text-3xl font-bold mt-2">
                {{ number_format($this->pendingLeaves) }}
            </h3>
            <a href="{{ route('leaves.index') }}" class="text-sm text-indigo-600 mt-2 inline-block hover:underline">
                Review requests &rarr;
            </a>
        </div>

    </div>

    {{-- Secondary Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mt-6">

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Open Tasks</p>
            <h3 class="text-3xl font-bold mt-2">{{ number_format($this->openTasks) }}</h3>
            <p class="text-sm text-slate-400 mt-1">
                {{ number_format($this->completedTasks) }} completed
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Pending Promotions</p>
            <h3 class="text-3xl font-bold mt-2">{{ number_format($this->pendingPromotions) }}</h3>
            <p class="text-sm text-slate-400 mt-1">
                Awaiting approval
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Newly Accepted Soldiers</p>
            <h3 class="text-3xl font-bold mt-2">{{ number_format($this->newlyAcceptedSoldiers) }}</h3>
            <p class="text-sm text-slate-400 mt-1">
                In the last 30 days
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Milestone</p>
            <h3 class="text-3xl font-bold mt-2">
                {{ $this->totalPersonnel > 0 ? round(($this->activeSoldiers / $this->totalPersonnel) * 100) : 0 }}%
            </h3>
            <p class="text-sm text-slate-400 mt-1">
                Active personnel rate
            </p>
        </div>

    </div>

    {{-- Personnel Status --}}
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-slate-900">
            Personnel Status
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Breakdown of all soldiers by current status.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mt-4">

            <div class="bg-white rounded-xl border border-gray-200 border-l-4 border-l-green-500 p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">Active Soldiers</p>
                    <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                </div>
                <h3 class="text-3xl font-bold mt-2">{{ number_format($this->activeSoldiers) }}</h3>
                <p class="text-sm text-green-600 mt-2">Currently serving</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 border-l-4 border-l-red-500 p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">Inactive Soldiers</p>
                    <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                </div>
                <h3 class="text-3xl font-bold mt-2">{{ number_format($this->inactiveSoldiers) }}</h3>
                <p class="text-sm text-red-600 mt-2">Not in active duty</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 border-l-4 border-l-slate-500 p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">Retired Soldiers</p>
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-500"></span>
                </div>
                <h3 class="text-3xl font-bold mt-2">{{ number_format($this->retiredSoldiers) }}</h3>
                <p class="text-sm text-slate-600 mt-2">Honorably discharged</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 border-l-4 border-l-blue-500 p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">On Leave</p>
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                </div>
                <h3 class="text-3xl font-bold mt-2">{{ number_format($this->onLeaveSoldiers) }}</h3>
                <p class="text-sm text-blue-600 mt-2">On approved leave</p>
            </div>

        </div>
    </div>

    {{-- Recent Personnel --}}
    <div class="mt-8 bg-white rounded-xl border border-gray-200">

        <div class="p-6 border-b border-gray-200">

            <div class="flex items-center justify-between">

                <div>
                    <h3 class="text-lg font-semibold">
                        Recent Personnel
                    </h3>

                    <p class="text-sm text-gray-500">
                        Recently added personnel records
                    </p>
                </div>

                <a
                    href="/personnel/create"
                    class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition"
                >
                    + Add Personnel
                </a>

            </div>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium">Name</th>
                        <th class="text-left px-6 py-3 font-medium">Rank</th>
                        <th class="text-left px-6 py-3 font-medium">Unit</th>
                        <th class="text-left px-6 py-3 font-medium">Status</th>
                        <th class="text-left px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($this->recentPersonnel as $personnel)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium">
                                <a href="{{ route('personnel.profile', $personnel->personnel_id) }}"
                                    class="flex items-center gap-3 text-slate-900 hover:text-slate-600 transition">
                                    <x-avatar :personnel="$personnel" size="sm"/>
                                    <span>{{ $personnel->last_name }}, {{ $personnel->first_name }}</span>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                {{ $personnel->rank?->rank_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $personnel->units?->unit_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <x-personnel.status-badge :status="$personnel->status" />
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('personnel.profile', $personnel->personnel_id) }}"
                                   class="text-indigo-600 hover:underline text-xs font-medium">
                                    View profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No personnel records yet.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
