<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Leaves;
use App\Models\User;
use App\Notifications\LeaveStatusNotification;
use Illuminate\Support\Facades\Notification;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $filter = 'pending';
    public string $search = '';

    public ?int $viewingLeaveId = null;
    public string $remarks = '';

    protected $queryString = ['filter'];

    #[Computed]
    public function statusBadge(string $status): array
    {
        return match ($status) {
            'approved' => ['bg-emerald-100 text-emerald-700', 'Approved'],
            'rejected' => ['bg-rose-100 text-rose-700', 'Rejected'],
            default => ['bg-amber-100 text-amber-700', 'Pending'],
        };
    }

    #[Computed]
    public function leaves()
    {
        return Leaves::query()
            ->with(['personnel', 'leaveType', 'approver'])
            ->when($this->filter, fn ($query) => $query->where('leaves.status', $this->filter))
            ->when($this->search, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('created_at')
            ->paginate(10);
    }

    public function viewLeave(int $leaveId)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $this->viewingLeaveId = $leaveId;
        $this->remarks = '';
    }

    public function approve()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $leave = Leaves::findOrFail($this->viewingLeaveId);
        $leave->update([
            'status' => 'approved',
            'remarks' => $this->remarks ?: null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->notifySoldier($leave);

        $this->viewingLeaveId = null;
        $this->remarks = '';
        session()->flash('status', 'Leave request approved.');
    }

    public function reject()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $this->validate([
            'remarks' => 'required|string|max:1000',
        ], [
            'remarks.required' => 'Please provide a reason for rejecting this request.',
        ]);

        $leave = Leaves::findOrFail($this->viewingLeaveId);
        $leave->update([
            'status' => 'rejected',
            'remarks' => $this->remarks,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->notifySoldier($leave);

        $this->viewingLeaveId = null;
        $this->remarks = '';
        session()->flash('status', 'Leave request rejected.');
    }

    private function notifySoldier(Leaves $leave): void
    {
        $soldier = User::find($leave->user_id);

        if ($soldier) {
            Notification::send($soldier, new LeaveStatusNotification($leave));
        }
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
};
?>

<div>
    <div class="max-w-7xl mx-auto mt-2">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Leave Management
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Review and manage leave requests.
                </p>
            </div>
            <a href="{{ route('leaves.create') }}"
               class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Leave Request
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
            <div class="flex rounded-lg border border-slate-200 bg-slate-50 p-1">
                <button
                    wire:click="$set('filter', 'pending')"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition
                           {{ $filter === 'pending' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    Pending
                </button>
                <button
                    wire:click="$set('filter', 'approved')"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition
                           {{ $filter === 'approved' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    Approved
                </button>
                <button
                    wire:click="$set('filter', 'rejected')"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition
                           {{ $filter === 'rejected' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    Rejected
                </button>
            </div>

            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search by soldier name..."
                class="w-full sm:w-64 border border-slate-300 rounded-lg px-4 py-2 text-sm">
        </div>

        {{-- Table --}}
        <div class="mx-auto overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Soldier</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Leave Type</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Start Date</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">End Date</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Days</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($this->leaves as $leave)
                        <tr wire:key="leave-{{ $leave->leave_id }}" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <x-avatar :personnel="$leave->personnel" size="sm"/>
                                    <div>
                                        <a href="{{ $leave->personnel ? route('personnel.profile', $leave->personnel->personnel_id) : '#' }}"
                                            class="font-medium text-slate-900 hover:text-slate-600 transition">
                                            {{ $leave->personnel?->last_name }}, {{ $leave->personnel?->first_name }}
                                        </a>
                                        <div class="text-xs text-slate-500">
                                            {{ $leave->personnel?->rank?->rank_name ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                {{ $leave->leaveType?->leave_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                {{ $leave->start_date?->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                {{ $leave->end_date?->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                {{ $leave->start_date?->diffInDays($leave->end_date) + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $badge = $this->statusBadge($leave->status); @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge[0] }}">
                                    {{ $badge[1] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    wire:click="viewLeave({{ $leave->leave_id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-indigo-600 border border-indigo-200 rounded-md hover:bg-indigo-50 transition duration-150">
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                No leave requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $this->leaves->links('components.pagination.personnel') }}
        </div>

    </div>

    {{-- Review Modal --}}
    @if ($viewingLeaveId)
        @php $viewLeave = Leaves::with(['personnel.rank', 'leaveType', 'approver'])->find($viewingLeaveId); @endphp
        @if ($viewLeave)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('viewingLeaveId', null)"></div>

            <div class="relative w-full max-w-lg mx-4 bg-white rounded-xl shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Leave Request</h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $viewLeave->personnel?->last_name }}, {{ $viewLeave->personnel?->first_name }}
                            &middot; {{ $viewLeave->personnel?->rank?->rank_name ?? 'No Rank' }}
                        </p>
                    </div>
                    <button wire:click="$set('viewingLeaveId', null)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Leave Type</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewLeave->leaveType?->leave_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Duration</dt>
                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $viewLeave->start_date?->format('M j, Y') }} &ndash; {{ $viewLeave->end_date?->format('M j, Y') }}
                                ({{ $viewLeave->start_date?->diffInDays($viewLeave->end_date) + 1 }} days)
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status</dt>
                            <dd class="mt-1">
                                @php $badge = $this->statusBadge($viewLeave->status); @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge[0] }}">
                                    {{ $badge[1] }}
                                </span>
                            </dd>
                        </div>
                        @if ($viewLeave->approver)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Reviewed By</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewLeave->approver->name }}</dd>
                        </div>
                        @endif
                    </dl>

                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Reason</dt>
                        <p class="mt-2 text-sm text-gray-700 bg-slate-50 rounded-lg p-3">{{ $viewLeave->reason }}</p>
                    </div>

                    @if ($viewLeave->remarks)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Reviewer Remarks</dt>
                        <p class="mt-2 text-sm text-gray-700 bg-slate-50 rounded-lg p-3">{{ $viewLeave->remarks }}</p>
                    </div>
                    @endif

                    @if ($viewLeave->status === 'pending')
                        <div class="border-t border-gray-200 pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Remarks <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                wire:model="remarks"
                                rows="3"
                                placeholder="Add remarks..."
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition"></textarea>
                            @error('remarks')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                    <button
                        wire:click="$set('viewingLeaveId', null)"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Close
                    </button>

                    @if ($viewLeave->status === 'pending')
                        <button
                            wire:click="reject"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            <span wire:loading.remove>Reject</span>
                            <span wire:loading>Saving...</span>
                        </button>
                        <button
                            wire:click="approve"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                            <span wire:loading.remove>Approve</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
