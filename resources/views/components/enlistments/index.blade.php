<?php

use App\Models\EnlistmentApplication;
use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Units;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $filter = 'pending';

    public string $search = '';

    public ?int $viewingApplicationId = null;

    public ?int $acceptRankId = null;

    public ?int $acceptUnitId = null;

    public string $remarks = '';

    protected $queryString = ['filter'];

    #[Computed]
    public function applications()
    {
        return EnlistmentApplication::with('documents', 'personnel')
            ->when($this->filter, fn ($query) => $query->where('status', $this->filter))
            ->when($this->search, function ($query) {
                $query->where('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('last_name', 'like', '%'.$this->search.'%')
                    ->orWhere('personal_email', 'like', '%'.$this->search.'%');
            })
            ->latest('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function ranks()
    {
        return Ranks::orderBy('level')->get();
    }

    #[Computed]
    public function units()
    {
        return Units::orderBy('unit_name')->get();
    }

    public function viewApplication(int $applicationId)
    {
        $this->viewingApplicationId = $applicationId;
        $this->acceptRankId = null;
        $this->acceptUnitId = null;
        $this->remarks = '';
        $this->resetValidation();
    }

    public function accept()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $this->validate([
            'acceptRankId' => 'required|integer|exists:ranks,rank_id',
            'acceptUnitId' => 'required|integer|exists:units,unit_id',
        ], [
            'acceptRankId.required' => 'Select an entry rank for the new soldier.',
            'acceptUnitId.required' => 'Select a unit to assign the new soldier to.',
        ]);

        $application = EnlistmentApplication::findOrFail($this->viewingApplicationId);

        abort_if($application->status !== 'pending', 403);

        $personnel = Personnel::create([
            'first_name' => $application->first_name,
            'middle_name' => $application->middle_name,
            'last_name' => $application->last_name,
            'suffix' => $application->suffix,
            'date_of_birth' => $application->date_of_birth,
            'gender' => $application->gender,
            'contact_number' => $application->contact_number,
            'personal_email' => $application->personal_email,
            'address' => $application->address,
            'date_of_entry' => now()->toDateString(),
            'status' => 'Active',
            'rank_id' => $this->acceptRankId,
            'unit_id' => $this->acceptUnitId,
        ]);

        $application->update([
            'status' => 'accepted',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'remarks' => $this->remarks ?: null,
            'personnel_id' => $personnel->personnel_id,
        ]);

        $this->remarks = '';
        session()->flash('status', 'Applicant accepted and enlisted as a soldier.');
    }

    public function reject()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $this->validate([
            'remarks' => 'required|string|max:1000',
        ], [
            'remarks.required' => 'Please provide a reason for rejecting this application.',
        ]);

        $application = EnlistmentApplication::findOrFail($this->viewingApplicationId);

        abort_if($application->status !== 'pending', 403);

        $application->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'remarks' => $this->remarks,
        ]);

        session()->flash('status', 'Application rejected.');
    }

    public function closeView()
    {
        $this->viewingApplicationId = null;
        $this->acceptRankId = null;
        $this->acceptUnitId = null;
        $this->remarks = '';
        $this->resetValidation();
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
                    Enlistment Applications
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Review recruitment applications and enlist accepted applicants as soldiers.
                </p>
            </div>
            <a href="{{ route('apply') }}" target="_blank"
               class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition inline-flex items-center gap-2">
                🔗 Application Form
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
            <div class="flex rounded-lg border border-slate-200 bg-slate-50 p-1">
                <button wire:click="$set('filter', 'pending')"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition {{ $filter === 'pending' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    Pending
                </button>
                <button wire:click="$set('filter', 'accepted')"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition {{ $filter === 'accepted' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    Accepted
                </button>
                <button wire:click="$set('filter', 'rejected')"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition {{ $filter === 'rejected' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    Rejected
                </button>
            </div>

            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search by name or email..."
                   class="w-full sm:w-64 border border-slate-300 rounded-lg px-4 py-2 text-sm">
        </div>

        {{-- Table --}}
        <div class="mx-auto overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Applicant</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Contact</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Documents</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Submitted</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($this->applications as $application)
                        <tr wire:key="app-{{ $application->id }}" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900">{{ $application->full_name }}</div>
                                <div class="text-xs text-slate-500">{{ $application->date_of_birth?->format('M j, Y') }} &bull; {{ $application->gender }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                <div>{{ $application->contact_number }}</div>
                                <div class="text-xs text-slate-500">{{ $application->personal_email }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $application->documents->count() }} file(s)</td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $application->created_at?->format('M j, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($application->status === 'accepted') bg-emerald-100 text-emerald-700
                                    @elseif ($application->status === 'rejected') bg-rose-100 text-rose-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    {{ ucfirst($application->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="viewApplication({{ $application->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-indigo-600 border border-indigo-200 rounded-md hover:bg-indigo-50 transition duration-150">
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                No applications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $this->applications->links('components.pagination.personnel') }}
        </div>

    </div>

    {{-- REVIEW MODAL --}}
    @if ($viewingApplicationId)
        @php $viewApp = EnlistmentApplication::with('documents', 'personnel', 'reviewer')->find($viewingApplicationId); @endphp
        @if ($viewApp)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" wire:click="closeView"></div>
            <div class="relative w-full max-w-2xl mx-4 bg-white rounded-xl shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Enlistment Application</h3>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $viewApp->full_name }}</p>
                    </div>
                    <button wire:click="closeView" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Date of Birth</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewApp->date_of_birth?->format('M j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewApp->gender }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Contact Number</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewApp->contact_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewApp->personal_email }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewApp->address }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($viewApp->status === 'accepted') bg-emerald-100 text-emerald-700
                                    @elseif ($viewApp->status === 'rejected') bg-rose-100 text-rose-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    {{ ucfirst($viewApp->status) }}
                                </span>
                            </dd>
                        </div>
                        @if ($viewApp->reviewer && $viewApp->reviewed_at)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Reviewed By</dt>
                                <dd class="mt-1 font-medium text-gray-900">
                                    {{ $viewApp->reviewer->name }}
                                    <span class="block text-xs font-normal text-slate-500">{{ $viewApp->reviewed_at->format('M j, Y g:i A') }}</span>
                                </dd>
                            </div>
                        @endif
                    </dl>

                    {{-- Enlisted soldier link --}}
                    @if ($viewApp->status === 'accepted' && $viewApp->personnel)
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <p class="text-sm text-emerald-800">
                                <strong>Enlisted as a soldier.</strong>
                                Enlisted as {{ $viewApp->personnel->rank?->rank_name ?? '—' }} under
                                {{ $viewApp->personnel->units?->unit_name ?? '—' }} on {{ $viewApp->reviewed_at?->format('M j, Y') }}.
                            </p>
                            <a href="{{ route('personnel.profile', $viewApp->personnel->personnel_id) }}"
                               class="inline-block mt-2 text-sm font-medium text-emerald-700 hover:underline">
                                View soldier profile &rarr;
                            </a>
                        </div>
                    @endif

                    {{-- Documents --}}
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Requirements ({{ $viewApp->documents->count() }})</dt>
                        <ul class="space-y-2">
                            @forelse ($viewApp->documents as $document)
                                <li>
                                    <a href="{{ $document->url }}" target="_blank"
                                       class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 transition">
                                        <span class="truncate">{{ $document->original_name }}</span>
                                        <span class="shrink-0 text-xs text-indigo-600 font-medium">Open &rarr;</span>
                                    </a>
                                </li>
                            @empty
                                <li class="text-sm text-slate-400">No supporting documents were uploaded.</li>
                            @endforelse
                        </ul>
                    </div>

                    @if ($viewApp->remarks)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Reviewer Remarks</dt>
                            <p class="mt-2 text-sm text-gray-700 bg-slate-50 rounded-lg p-3">{{ $viewApp->remarks }}</p>
                        </div>
                    @endif

                    {{-- Pending actions --}}
                    @if ($viewApp->status === 'pending')
                        <div class="border-t border-gray-200 pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Entry Rank (on acceptance)</label>
                                <select wire:model="acceptRankId"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                                    <option value="">Select rank</option>
                                    @foreach ($this->ranks as $rank)
                                        <option value="{{ $rank->rank_id }}">{{ $rank->rank_name }}</option>
                                    @endforeach
                                </select>
                                @error('acceptRankId') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Assign Unit (on acceptance)</label>
                                <select wire:model="acceptUnitId"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                                    <option value="">Select unit</option>
                                    @foreach ($this->units as $unit)
                                        <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}</option>
                                    @endforeach
                                </select>
                                @error('acceptUnitId') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Remarks <span class="text-red-500">* required when rejecting</span>
                            </label>
                            <textarea wire:model="remarks" rows="3" placeholder="Reason for rejecting, or notes for acceptance..."
                                      class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition"></textarea>
                            @error('remarks') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                    <button wire:click="closeView"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Close
                    </button>
                    @if ($viewApp->status === 'pending')
                        <button wire:click="reject" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            <span wire:loading.remove>Reject</span>
                            <span wire:loading>Saving...</span>
                        </button>
                        <button wire:click="accept" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                            <span wire:loading.remove>Accept &amp; Enlist</span>
                            <span wire:loading>Saving...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endif
    @endif
</div>