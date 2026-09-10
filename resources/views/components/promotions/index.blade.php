<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Personnel;
use App\Models\Promotions;
use App\Models\Ranks;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $filter = 'pending';
    public string $search = '';

    public bool $showCreateModal = false;
    public ?int $viewingPromotionId = null;

    public ?int $personnel_id = null;
    public ?int $from_rank_id = null;
    public ?int $to_rank_id = null;
    public string $promotion_date = '';
    public string $remarks = '';
    public string $recommendation = '';

    protected $queryString = ['filter'];

    #[Computed]
    public function canRecommend(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return (int) ($user->personnel?->rank?->level ?? 0) >= 12;
    }

    #[Computed]
    public function personnels()
    {
        $excludedId = auth()->user()?->role !== 'admin'
            ? auth()->user()?->personnel?->personnel_id ?? 0
            : null;

        return Personnel::with('rank')
            ->when($excludedId, fn ($query) => $query->where('personnel_id', '!=', $excludedId))
            ->orderBy('last_name')
            ->get();
    }

    #[Computed]
    public function ranks()
    {
        return Ranks::orderBy('level')->get();
    }

    #[Computed]
    public function promotions()
    {
        return Promotions::query()
            ->with(['personnel.rank', 'fromRank', 'toRank', 'recommender'])
            ->when($this->filter, fn ($query) => $query->where('status', $this->filter))
            ->when($this->search, function ($query) {
                $query->whereHas('personnel', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                });
            })
            ->latest('created_at')
            ->paginate(10);
    }

    public function openCreate()
    {
        abort_unless($this->canRecommend, 403);

        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function storePromotion()
    {
        abort_unless($this->canRecommend, 403);

        $rules = [
            'personnel_id' => 'required|integer|exists:personnels,personnel_id',
            'from_rank_id' => 'required|integer|exists:ranks,rank_id',
            'to_rank_id' => 'required|integer|exists:ranks,rank_id|different:from_rank_id',
            'promotion_date' => 'required|date',
            'remarks' => 'nullable|string|max:1000',
            'recommendation' => 'nullable|string|max:1000',
        ];

        if (auth()->user()?->role !== 'admin') {
            $ownPersonnelId = auth()->user()?->personnel?->personnel_id;

            $rules['personnel_id'] = [
                'required',
                'integer',
                'exists:personnels,personnel_id',
                function (string $attribute, mixed $value, \Closure $fail) use ($ownPersonnelId) {
                    if ($ownPersonnelId !== null && (int) $value === (int) $ownPersonnelId) {
                        $fail('You cannot recommend yourself for promotion.');
                    }
                },
            ];
        }

        $validatedData = $this->validate($rules);

        $validatedData['remarks'] ??= '';
        $validatedData['status'] = 'pending';
        $validatedData['recommended_by'] = auth()->id();

        Promotions::create($validatedData);

        $this->resetForm();
        session()->flash('status', 'Promotion recommended for review.');
    }

    public function viewPromotion(int $promotionId)
    {
        $this->viewingPromotionId = $promotionId;
        $this->remarks = '';
    }

    public function approve()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $promotion = Promotions::findOrFail($this->viewingPromotionId);
        $promotion->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'remarks' => $this->remarks ?: $promotion->remarks,
        ]);

        if ($promotion->to_rank_id) {
            Personnel::where('personnel_id', $promotion->personnel_id)
                ->update(['rank_id' => $promotion->to_rank_id]);
        }

        $this->viewingPromotionId = null;
        $this->remarks = '';
        session()->flash('status', 'Promotion approved and rank updated.');
    }

    public function reject()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $this->validate([
            'remarks' => 'required|string|max:1000',
        ], [
            'remarks.required' => 'Please provide a reason for rejecting this promotion.',
        ]);

        $promotion = Promotions::findOrFail($this->viewingPromotionId);
        $promotion->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'remarks' => $this->remarks,
        ]);

        $this->viewingPromotionId = null;
        $this->remarks = '';
        session()->flash('status', 'Promotion recommendation rejected.');
    }

    public function updatedPersonnelId($value)
    {
        if ($value) {
            $personnel = Personnel::find($value);
            $this->from_rank_id = $personnel?->rank_id;
        }
    }

    public function resetForm()
    {
        $this->personnel_id = null;
        $this->from_rank_id = null;
        $this->to_rank_id = null;
        $this->promotion_date = '';
        $this->remarks = '';
        $this->recommendation = '';
        $this->showCreateModal = false;
        $this->viewingPromotionId = null;
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
                    Promotion Management
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Recommend soldiers for promotion and review recommendations.
                </p>
                @if (! $this->canRecommend)
                    <p class="mt-1 text-xs text-slate-400">
                        Only officers (Captain and above) can submit recommendations.
                    </p>
                @endif
            </div>
            @if ($this->canRecommend)
            <button wire:click="openCreate"
                    class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Recommend Promotion
            </button>
            @endif
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
            <div class="flex rounded-lg border border-slate-200 bg-slate-50 p-1">
                <button wire:click="$set('filter', 'pending')"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition {{ $filter === 'pending' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    Pending
                </button>
                <button wire:click="$set('filter', 'approved')"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition {{ $filter === 'approved' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    Approved
                </button>
                <button wire:click="$set('filter', 'rejected')"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition {{ $filter === 'rejected' ? 'bg-white text-indigo-600 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                    Rejected
                </button>
            </div>

            <input type="text"
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
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">From Rank</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">To Rank</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Promotion Date</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($this->promotions as $promotion)
                        <tr wire:key="promo-{{ $promotion->promotion_id }}" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900">
                                    {{ $promotion->personnel?->last_name }}, {{ $promotion->personnel?->first_name }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $promotion->personnel?->rank?->rank_name ?? '—' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $promotion->fromRank?->rank_name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $promotion->toRank?->rank_name ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $promotion->promotion_date?->format('M j, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($promotion->status === 'approved') bg-emerald-100 text-emerald-700
                                    @elseif ($promotion->status === 'rejected') bg-rose-100 text-rose-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    {{ ucfirst($promotion->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="viewPromotion({{ $promotion->promotion_id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-indigo-600 border border-indigo-200 rounded-md hover:bg-indigo-50 transition duration-150">
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                No promotion recommendations found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $this->promotions->links('components.pagination.personnel') }}
        </div>

    </div>

    {{-- CREATE MODAL --}}
    @if ($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" wire:click="resetForm"></div>
        <div class="relative w-full max-w-lg mx-4 bg-white rounded-xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Recommend Promotion</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Select a soldier and target rank.</p>
                </div>
                <button wire:click="resetForm" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="storePromotion" class="p-6 space-y-5">
@csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Soldier</label>
                    <select wire:model="personnel_id"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                        <option value="">Select soldier</option>
                        @foreach ($this->personnels as $personnel)
                            <option value="{{ $personnel->personnel_id }}">
                                {{ $personnel->last_name }}, {{ $personnel->first_name }} ({{ $personnel->rank?->rank_name ?? 'No Rank' }})
                            </option>
                        @endforeach
                    </select>
                    @error('personnel_id') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Rank</label>
                        <select wire:model="from_rank_id" disabled
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm bg-gray-50">
                            <option value="">Auto-filled</option>
                        </select>
                        @error('from_rank_id') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Rank</label>
                        <select wire:model="to_rank_id"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            <option value="">Select rank</option>
                            @foreach ($this->ranks as $rank)
                                <option value="{{ $rank->rank_id }}">{{ $rank->rank_name }}</option>
                            @endforeach
                        </select>
                        @error('to_rank_id') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Promotion Date</label>
                    <input type="date" wire:model="promotion_date"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                    @error('promotion_date') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recommendation</label>
                    <textarea wire:model="recommendation" rows="3"
                              placeholder="Reason for recommending this promotion..."
                              class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition"></textarea>
                    @error('recommendation') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-200">
                    <button type="button" wire:click="resetForm"
                            class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-700 transition">
                        <span wire:loading.remove>Submit Recommendation</span>
                        <span wire:loading>Submitting...</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
    @endif

    {{-- REVIEW MODAL --}}
    @if ($viewingPromotionId)
        @php $viewPromo = Promotions::with(['personnel.rank', 'fromRank', 'toRank', 'recommender', 'approver'])->find($viewingPromotionId); @endphp
        @if ($viewPromo)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('viewingPromotionId', null)"></div>
            <div class="relative w-full max-w-lg mx-4 bg-white rounded-xl shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Promotion Recommendation</h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $viewPromo->personnel?->last_name }}, {{ $viewPromo->personnel?->first_name }}
                        </p>
                    </div>
                    <button wire:click="$set('viewingPromotionId', null)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">From Rank</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewPromo->fromRank?->rank_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">To Rank</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewPromo->toRank?->rank_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Recommended By</dt>
                            <dd class="mt-1 font-medium text-gray-900">
                                @if ($viewPromo->recommender)
                                    {{ $viewPromo->recommender->name }}
                                    @if ($viewPromo->recommender->personnel?->rank)
                                        <span class="block text-xs font-normal text-slate-500">
                                            {{ $viewPromo->recommender->personnel->rank->rank_name }}
                                        </span>
                                    @endif
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Promotion Date</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewPromo->promotion_date?->format('M j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($viewPromo->status === 'approved') bg-emerald-100 text-emerald-700
                                    @elseif ($viewPromo->status === 'rejected') bg-rose-100 text-rose-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    {{ ucfirst($viewPromo->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>

                    @if ($viewPromo->recommendation)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Recommendation</dt>
                        <p class="mt-2 text-sm text-gray-700 bg-slate-50 rounded-lg p-3">{{ $viewPromo->recommendation }}</p>
                    </div>
                    @endif

                    @if ($viewPromo->remarks && $viewPromo->status !== 'pending')
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Reviewer Remarks</dt>
                        <p class="mt-2 text-sm text-gray-700 bg-slate-50 rounded-lg p-3">{{ $viewPromo->remarks }}</p>
                    </div>
                    @endif

                    @if ($viewPromo->status === 'pending' && auth()->user()->role === 'admin')
                    <div class="border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Remarks <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="remarks" rows="3"
                                  class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition"></textarea>
                        @error('remarks') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                    <button wire:click="$set('viewingPromotionId', null)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Close
                    </button>
                    @if ($viewPromo->status === 'pending' && auth()->user()->role === 'admin')
                        <button wire:click="reject" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                            <span wire:loading.remove>Reject</span>
                            <span wire:loading>Saving...</span>
                        </button>
                        <button wire:click="approve" wire:loading.attr="disabled"
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
