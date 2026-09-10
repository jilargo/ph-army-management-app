<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Personnel;

new #[Layout('layouts.app')] class extends Component
{
    public ?int $personnelId = null;

    public function mount(int $personnel)
    {
        $this->personnelId = $personnel;
    }

    #[Computed]
    public function personnel()
    {
        return Personnel::with([
            'rank',
            'units',
            'tasks.personnel',
            'leaves.leaveType',
            'promotions.fromRank',
            'promotions.toRank',
            'assignments.unit',
            'assignments.rank',
            'trainings.courses',
        ])->findOrFail($this->personnelId);
    }

    #[Computed]
    public function taskStats(): array
    {
        $tasks = $this->personnel->tasks;

        return [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
        ];
    }
};
?>

<div>
    <div class="max-w-7xl mx-auto mt-2">

        {{-- Profile Header --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-slate-800 to-slate-600"></div>

            <div class="px-6 pb-6">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between -mt-10 gap-4">
                    <div class="flex items-end gap-4">
                        <x-avatar :personnel="$this->personnel" size="xl" class="border-4 border-white shadow-lg"/>
                        <div class="pb-1">
                            <h1 class="text-2xl font-bold text-slate-900">
                                {{ $this->personnel->rank?->rank_name ?? 'No Rank' }}
                                {{ $this->personnel->last_name }}, {{ $this->personnel->first_name }}
                                {{ $this->personnel->suffix }}
                            </h1>
                            <p class="text-sm text-slate-500">
                                {{ $this->personnel->units?->unit_name ?? 'No Unit Assigned' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-3 pb-1">
                        <a href="{{ route('personnel.edit', $this->personnel->personnel_id) }}"
                           class="px-4 py-2 text-sm font-medium text-indigo-600 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition">
                            Edit Profile
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status</p>
                        <x-personnel.status-badge :status="$this->personnel->status" class="mt-1" />
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ $this->personnel->gender }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Date of Entry</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ $this->personnel->date_of_entry }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Contact</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ $this->personnel->contact_number ?? '—' }}</p>
                        <p class="text-xs text-slate-500">{{ $this->personnel->personal_email ?? '' }}</p>
                    </div>

                </div>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Total Tasks</p>
                <h3 class="text-3xl font-bold mt-2">{{ $this->taskStats['total'] }}</h3>
                <p class="text-sm text-slate-400 mt-1">
                    {{ $this->taskStats['completed'] }} completed
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Leave Requests</p>
                <h3 class="text-3xl font-bold mt-2">{{ $this->personnel->leaves->count() }}</h3>
                <p class="text-sm text-slate-400 mt-1">
                    {{ $this->personnel->leaves->where('status', 'approved')->count() }} approved
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Promotions</p>
                <h3 class="text-3xl font-bold mt-2">{{ $this->personnel->promotions->count() }}</h3>
                <p class="text-sm text-slate-400 mt-1">
                    {{ $this->personnel->promotions->where('status', 'approved')->count() }} approved
                </p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Trainings</p>
                <h3 class="text-3xl font-bold mt-2">{{ $this->personnel->trainings->count() }}</h3>
                <p class="text-sm text-slate-400 mt-1">
                    Assignments: {{ $this->personnel->assignments->count() }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

            {{-- Recent Tasks --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Recent Tasks</h3>
                    <span class="text-xs text-slate-500">{{ $this->personnel->tasks->count() }} total</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($this->personnel->tasks->take(5) as $task)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                <p class="text-xs text-slate-500">{{ $task->due_date?->format('M j, Y') }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($task->status === 'completed') bg-emerald-100 text-emerald-700
                                @elseif ($task->status === 'in_progress') bg-amber-100 text-amber-700
                                @else bg-slate-100 text-slate-600 @endif">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-sm text-slate-500 text-center">No tasks assigned.</p>
                    @endforelse
                </div>
            </div>

            {{-- Leave History --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Leave History</h3>
                    <span class="text-xs text-slate-500">{{ $this->personnel->leaves->count() }} total</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($this->personnel->leaves->take(5) as $leave)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $leave->leaveType?->leave_name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $leave->start_date?->format('M j') }} &ndash; {{ $leave->end_date?->format('M j, Y') }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($leave->status === 'approved') bg-emerald-100 text-emerald-700
                                @elseif ($leave->status === 'rejected') bg-rose-100 text-rose-700
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-sm text-slate-500 text-center">No leave requests.</p>
                    @endforelse
                </div>
            </div>

            {{-- Promotion History --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Promotion History</h3>
                    <span class="text-xs text-slate-500">{{ $this->personnel->promotions->count() }} total</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($this->personnel->promotions->take(5) as $promotion)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $promotion->fromRank?->rank_name }} &rarr; {{ $promotion->toRank?->rank_name }}
                                </p>
                                <p class="text-xs text-slate-500">{{ $promotion->promotion_date?->format('M j, Y') }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if ($promotion->status === 'approved') bg-emerald-100 text-emerald-700
                                @elseif ($promotion->status === 'rejected') bg-rose-100 text-rose-700
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ ucfirst($promotion->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-sm text-slate-500 text-center">No promotions recorded.</p>
                    @endforelse
                </div>
            </div>

            {{-- Assignment History --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Assignment History</h3>
                    <span class="text-xs text-slate-500">{{ $this->personnel->assignments->count() }} total</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($this->personnel->assignments->take(5) as $assignment)
                        <div class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $assignment->position ?? 'Assignment' }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $assignment->unit?->unit_name ?? '—' }}
                                &middot; {{ $assignment->rank?->rank_name ?? '—' }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $assignment->start_date?->format('M Y') }}
                                @if ($assignment->end_date)
                                    &ndash; {{ $assignment->end_date->format('M Y') }}
                                @else
                                    &ndash; Present
                                @endif
                            </p>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-sm text-slate-500 text-center">No assignments recorded.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</div>
