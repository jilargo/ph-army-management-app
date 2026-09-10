<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Personnel;
use App\Models\Units;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $unit_id = null;

    #[Computed]
    public function units()
    {
        return Units::orderBy('unit_name')->get();
    }

    #[Computed]
    public function personnels()
    {
        $personnel = Personnel::query()
            ->latest()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('middle_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhereHas('rank', fn ($q) => $q->where('rank_name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->unit_id, fn ($query) => $query->where('unit_id', $this->unit_id))
            ->paginate(7);

        $personnel->load('rank', 'units');

        return $personnel;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUnitId(): void
    {
        $this->resetPage();
    }


    // Holds the ID of the personnel selected for deletion.
// Also used to determine whether the delete modal should be displayed.
public ?int $personnelToDelete = null;

// Holds the complete Personnel model.
// Used by the modal to display the selected personnel's information.
public ?Personnel $personnelBeingDeleted = null;

public function confirmDelete(int $personnel_id)
{
    // Step 1: Store the selected ID.
    // Changing this from null to an ID causes Livewire to re-render the component,
    // which makes the modal appear when using @if($personnelToDelete).
    $this->personnelToDelete = $personnel_id;

    // Step 2: Find the corresponding Personnel record from the database.
    // The returned model is stored so the modal can display its data.
    $this->personnelBeingDeleted = Personnel::findOrFail($personnel_id);

    // Debug: inspect the Personnel model.
    // dd($this->personnelBeingDeleted);

    // Debug: inspect the Personnel's related Rank model.
    // dd($this->personnelBeingDeleted->rank);
}

    public function deletePersonnel()
    {
        Personnel::findOrFail($this->personnelToDelete)->delete();
        $this->personnelToDelete = null;
        session()->flash('status', 'Personnel Successfully Deleted');
        $this->redirectRoute('personnel.index');
    }
};

?>

<div class="w-full min-w-0 mt-3" >

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 mx-3">

        <h1 class="text-2xl font-bold text-slate-900">
            Personnel List
        </h1>

        <div class="flex items-center gap-3 w-full sm:w-auto">

            <div class="relative flex-1 sm:w-64">
                <input
                    type="text"
                    wire:model.live="search"
                    placeholder="Search personnel..."
                    class="w-full border border-slate-300 rounded-lg pl-10 pr-4 py-2 text-sm">
            </div>

            <select
                wire:model.live="unit_id"
                class="w-full sm:w-48 border border-slate-300 rounded-lg px-4 py-2 text-sm bg-white">
                <option value="">All Units</option>
                @foreach ($this->units as $unit)
                    <option value="{{ $unit->unit_id }}">{{ $unit->unit_name }}</option>
                @endforeach
            </select>

            <a
                    href="/personnel/create"
                    class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition"
                >
                    + Add Personnel
                </a>

        </div>

    </div>

    {{--MODAL FOR DELETE --}}
    @if ($personnelToDelete)
    <div class="fixed inset-0 z-50 flex items-center justify-center">

        {{-- Background --}}
        <div class="absolute inset-0 bg-black/50"></div>

        {{-- Modal --}}
        <div class="relative w-full max-w-md mx-4 bg-white rounded-xl shadow-xl p-6">

            <div class="flex items-start gap-4">

                {{-- Warning Icon --}}
                <div class="flex-shrink-0 flex items-center justify-center
                            w-10 h-10 rounded-full bg-red-100">

                    <svg class="w-5 h-5 text-red-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01
                                 M12 5.5a6.5 6.5 0 110 13
                                 6.5 6.5 0 010-13z" />
                    </svg>

                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        Delete
                        <span class="text-blue-600">
                            {{ $personnelBeingDeleted->rank?->rank_name }}
                            {{ $personnelBeingDeleted->first_name }}
                            {{ $personnelBeingDeleted->last_name }}
                        </span>
                        ?
                    </h3>

                    <p class="mt-2 text-sm text-gray-600">
                        Are you sure you want to delete this personnel?
                    </p>

                    <p class="mt-1 text-sm text-red-600">
                        This action cannot be undone.
                    </p>
                </div>

            </div>

            {{-- Buttons --}}
            <div class="flex justify-end gap-3 mt-6">

                <button
                    type="button"
                    wire:click="$set('personnelToDelete', null)"
                    class="px-4 py-2 text-sm font-medium
                           text-gray-700 bg-white
                           border border-gray-300 rounded-lg
                           hover:bg-gray-50 transition">

                    Cancel
                </button>

                <button
                    type="button"
                    wire:click="deletePersonnel"
                    class="px-4 py-2 text-sm font-medium
                           text-white bg-red-600 rounded-lg
                           hover:bg-red-700 transition">

                    Delete
                </button>

            </div>

        </div>
    </div>
    @endif



    {{-- TABLE CONTAINER --}}
    <div class="mx-3 w-auto overflow-x-auto rounded-xl border border-slate-200 shadow-sm">

        <table class="min-w-max divide-y divide-slate-200 text-sm">

            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Rank
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Unit
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Name
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Middle Name
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Last Name
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Suffix
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Date of Birth
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Gender
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Contact Number
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Email
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Address
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Date of Entry
                    </th>

                    
                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Status
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        System Time Entry
                    </th>

                    <th class="px-6 py-4 text-left font-semibold text-slate-700 whitespace-nowrap">
                        Actions
                    </th>

                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">

                @foreach ($this->personnels as $personnel)

                <tr
                    wire:key="{{ $personnel->personnel_id }}"
                    class="hover:bg-slate-50 transition-colors">

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->rank->rank_name }}
                    </td>
                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->units?->unit_name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                        <a href="{{ route('personnel.profile', $personnel->personnel_id) }}"
                            class="flex items-center gap-3 hover:text-slate-600 transition">
                            <x-avatar :personnel="$personnel" size="sm"/>
                            <span>
                                {{ $personnel->first_name }}
                            </span>
                        </a>
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->middle_name }}
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->last_name }}
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->suffix }}
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->date_of_birth }}
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->gender }}
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->contact_number }}
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->personal_email }}
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->address }}
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->date_of_entry }}
                    </td>

                    

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        <x-personnel.status-badge :status="$personnel->status" />
                    </td>

                    <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                        {{ $personnel->created_at }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">

                            {{-- View --}}
                            <a href="{{ route('personnel.profile', $personnel->personnel_id) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5
                                       text-sm font-medium text-slate-600
                                       border border-slate-200 rounded-md
                                       hover:bg-slate-50 hover:text-slate-900
                                       transition duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('personnel.edit', $personnel->personnel_id) }}"

                                class="inline-flex items-center gap-1.5 px-3 py-1.5
              text-sm font-medium text-indigo-600
              border border-indigo-200 rounded-md
              hover:bg-indigo-50 hover:text-indigo-700
              transition duration-150">

                                {{-- Edit Icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2">

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                            m-9-4l9-9m0 0l3 3m-3-3v5" />
                                </svg>

                                Edit
                            </a>


                            {{-- Delete --}}
                            <button type="button"
                                wire:click="confirmDelete( {{ $personnel->personnel_id }} )"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5
                                        text-sm font-medium text-red-600
                                        border border-red-200 rounded-md
                                        hover:bg-red-50 hover:text-red-700
                                        transition duration-150">

                                {{-- Delete Icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2">

                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862
                                           a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6
                                           M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3
                                           m-7 0h10" />
                                </svg>

                                Delete
                            </button>

                        </div>

    </td>

    </tr>

    @endforeach

    </tbody>

    </table>


</div>
<div class="mt-6 mx-4">
    {{ $this->personnels->links('components.pagination.personnel') }}
</div>


</div>