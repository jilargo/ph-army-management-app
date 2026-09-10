<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Leaves;

new #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function personnel()
    {
        return auth()->user()->personnel;
    }

    #[Computed]
    public function leaves()
    {
        $personnelId = auth()->user()->personnel?->personnel_id;

        if (! $personnelId) {
            return collect();
        }

        return Leaves::query()
            ->with(['leaveType', 'approver'])
            ->where('personnel_id', $personnelId)
            ->latest('created_at')
            ->get();
    }
};
?>

<div>
    <div>

        {{-- Welcome Header --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <x-avatar :personnel="$this->personnel" size="lg"/>
                <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Welcome, {{ auth()->user()->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    @if ($this->personnel)
                        {{ $this->personnel->rank?->rank_name ?? 'Soldier' }} —
                        {{ $this->personnel->last_name }}, {{ $this->personnel->first_name }} {{ $this->personnel->suffix }}
                    @else
                        No linked personnel record found. Contact your administrator.
                    @endif
                </p>
                </div>
            </div>

            <a href="{{ route('leaves.create') }}"
               class="inline-flex items-center gap-2 bg-slate-900 text-white px-4 py-2.5 rounded-lg text-sm hover:bg-slate-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                File Leave
            </a>
        </div>

        {{-- Leave History --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">
                        My Leave Requests
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Track the status of your submitted leave applications.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Leave Type</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Start Date</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">End Date</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Status</th>
                            <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($this->leaves as $leave)
                            <tr wire:key="leave-{{ $leave->leave_id }}" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-slate-900 whitespace-nowrap font-medium">
                                    {{ $leave->leaveType?->leave_name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                    {{ $leave->start_date?->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                    {{ $leave->end_date?->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if ($leave->status === 'approved') bg-emerald-100 text-emerald-700
                                        @elseif ($leave->status === 'rejected') bg-rose-100 text-rose-700
                                        @else bg-amber-100 text-amber-700 @endif">
                                        {{ ucfirst($leave->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $leave->remarks ?: '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    You have not filed any leave requests yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>