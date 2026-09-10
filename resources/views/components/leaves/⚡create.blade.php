<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Leaves;
use App\Models\LeaveType;
use App\Models\Personnel;
use App\Models\User;
use App\Notifications\LeaveSubmittedNotification;
use Illuminate\Support\Facades\Notification;

new #[Layout('layouts.app')] class extends Component
{
    public $LeaveTypes = [];
    public ?int $personnel_id = null;
    public ?int $leave_type_id = null;
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';

    public function mount()
    {
        $this->LeaveTypes = LeaveType::all();

        // Non-admin users file leave only for their own linked personnel record
        if (auth()->user()->role !== 'admin' && auth()->user()->personnel) {
            $this->personnel_id = auth()->user()->personnel->personnel_id;
        }
    }

    public function store_leave()
    {
        // Soldiers may only file a leave for their own linked personnel record
        if (auth()->user()->role !== 'admin') {
            $ownPersonnelId = auth()->user()->personnel?->personnel_id;
            abort_unless($ownPersonnelId && (int) $this->personnel_id === (int) $ownPersonnelId, 403);
        }

        $validatedData = $this->validate([
            'personnel_id' => 'required|integer|exists:personnels,personnel_id',
            'leave_type_id' => 'required|integer|exists:leave_types,leave_type_id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $validatedData['user_id'] = auth()->id();
        $validatedData['status'] = 'pending';

        $leave = Leaves::create($validatedData);

        Notification::send(
            User::where('role', 'admin')->get(),
            new LeaveSubmittedNotification($leave)
        );

        session()->flash('status', 'Leave request submitted for approval.');
        $this->redirect(auth()->user()->role === 'admin' ? route('leaves.index') : route('user-dashboard'));
    }
};
?>

<div>
    <div class="max-w-3xl mx-auto mt-2">
        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">
                File Leave Request
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Submit a leave application for review and approval.
            </p>
        </div>

        <form wire:submit="store_leave" class="space-y-8">
@csrf
            {{-- Leave Details --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

                <div class="px-6 py-5 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Leave Details
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Specify the type and duration of your leave.
                    </p>
                </div>

                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Personnel --}}
                        <div>
                            <label for="personnel_id"
                                   class="block text-sm font-medium text-gray-700 mb-2">
                                Personnel
                            </label>

                            @if (auth()->user()->role === 'admin')
                            <select id="personnel_id"
                                    wire:model="personnel_id"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                                <option value="">Select personnel</option>
                                @foreach (\App\Models\Personnel::all() as $personnel)
                                    <option value="{{ $personnel->personnel_id }}">
                                        {{ $personnel->last_name }}, {{ $personnel->first_name }}
                                    </option>
                                @endforeach
                            </select>
                            @else
                                @php
                                    $ownPersonnel = auth()->user()->personnel;
                                @endphp
                                <input type="text"
                                       disabled
                                       value="{{ $ownPersonnel ? $ownPersonnel->last_name . ', ' . $ownPersonnel->first_name : 'No linked personnel record' }}"
                                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm bg-gray-100">
                            @endif
                            @error('personnel_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Leave Type --}}
                        <div>
                            <label for="leave_type_id"
                                   class="block text-sm font-medium text-gray-700 mb-2">
                                Leave Type
                            </label>
                            <select id="leave_type_id"
                                    wire:model="leave_type_id"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                                <option value="">Select leave type</option>
                                @foreach ($LeaveTypes as $leaveType)
                                    <option value="{{ $leaveType->leave_type_id }}">
                                        {{ $leaveType->leave_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('leave_type_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Start Date --}}
                        <div>
                            <label for="start_date"
                                   class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date
                            </label>
                            <input type="date"
                                   id="start_date"
                                   wire:model="start_date"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            @error('start_date')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- End Date --}}
                        <div>
                            <label for="end_date"
                                   class="block text-sm font-medium text-gray-700 mb-2">
                                End Date
                            </label>
                            <input type="date"
                                   id="end_date"
                                   wire:model="end_date"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            @error('end_date')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Reason --}}
                        <div class="md:col-span-2">
                            <label for="reason"
                                   class="block text-sm font-medium text-gray-700 mb-2">
                                Reason
                            </label>
                            <textarea id="reason"
                                      wire:model="reason"
                                      rows="4"
                                      placeholder="Provide a detailed reason for your leave request"
                                      class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition"></textarea>
                            @error('reason')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                </div>

            </div>

            {{-- Form Actions --}}
            <div class="flex items-center justify-end gap-3">

                <a href="{{ route(auth()->user()->role === 'admin' ? 'leaves.index' : 'user-dashboard') }}"
                   class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-700 transition">
                    <span wire:loading.remove>Submit Request</span>
                    <span wire:loading>Submitting...</span>
                </button>

            </div>

        </form>

    </div>
</div>
