<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Units;
use App\Models\ParentUnit;

new #[Layout('layouts.app')] class extends Component
{
    public ?int $parent_id = null;
    public string $new_parent_name = '';
    public string $unit_name = '';
    public string $unit_code = '';
    public string $location = '';
    public string $status = 'Active';

    #[Computed]
    public function parents()
    {
        return ParentUnit::orderBy('parent_name')->get();
    }

    #[Computed]
    public function units()
    {
        return Units::with('parent')->orderBy('unit_name')->get();
    }

    public function storeUnit()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $rules = [
            'unit_name' => 'required|string|max:255',
            'unit_code' => 'required|string|max:20|unique:units,unit_code',
            'location' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ];

        if (trim($this->new_parent_name) === '') {
            $rules['parent_id'] = 'required|integer|exists:parent_units,parent_id';
        } else {
            $rules['new_parent_name'] = 'required|string|max:255';
        }

        $validated = $this->validate($rules);

        if (trim($this->new_parent_name) !== '') {
            $parent = ParentUnit::create(['parent_name' => trim($this->new_parent_name)]);
            $parentId = $parent->parent_id;
        } else {
            $parentId = $this->parent_id;
        }

        Units::create([
            'parent_id' => $parentId,
            'unit_name' => $validated['unit_name'],
            'unit_code' => $validated['unit_code'],
            'location' => $validated['location'],
            'status' => $validated['status'],
        ]);

        $this->reset(['parent_id', 'new_parent_name', 'unit_name', 'unit_code', 'location']);
        $this->status = 'Active';
        $this->resetValidation();

        session()->flash('status', 'Unit successfully added.');
    }
};
?>

<div>
    <div class="max-w-7xl mx-auto mt-2">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Unit Management
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Add battalions, brigades, regiments, and squadrons under an army division or command.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- ADD UNIT FORM --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-6 h-fit">
                <h2 class="text-lg font-semibold text-slate-900 mb-5">Add Unit</h2>

                <form wire:submit="storeUnit" class="space-y-5">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Division / Command</label>
                        <select wire:model="parent_id"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            <option value="">Select division or command...</option>
                            @foreach ($this->parents as $parent)
                                <option value="{{ $parent->parent_id }}">{{ $parent->parent_name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="h-px flex-1 bg-gray-200"></span>
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-400">or</span>
                        <span class="h-px flex-1 bg-gray-200"></span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            New Division / Command <span class="font-normal text-slate-400">(optional)</span>
                        </label>
                        <input type="text" wire:model="new_parent_name" placeholder="e.g. Armor Division, 5th Infantry Division..."
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                        @error('new_parent_name') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Unit Name</label>
                        <input type="text" wire:model="unit_name" placeholder="e.g. 1st Cavalry Squadron"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                        @error('unit_name') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Unit Code</label>
                        <input type="text" wire:model="unit_code" placeholder="e.g. 1CAVSQ"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                        @error('unit_code') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" wire:model="location" placeholder="e.g. Camp O'Donnell, Capas, Tarlac"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                        @error('location') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select wire:model="status"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        @error('status') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full bg-slate-900 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-700 transition">
                        <span wire:loading.remove>Add Unit</span>
                        <span wire:loading>Adding...</span>
                    </button>

                </form>
            </div>

            {{-- UNIT LIST --}}
            <div class="lg:col-span-3 bg-white rounded-xl border border-slate-200 shadow-sm p-6 h-fit">
                <h2 class="text-lg font-semibold text-slate-900 mb-5">Units</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 whitespace-nowrap">Unit</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 whitespace-nowrap">Code</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 whitespace-nowrap">Division / Command</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 whitespace-nowrap">Location</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 whitespace-nowrap">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($this->units as $unit)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{{ $unit->unit_name }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $unit->unit_code }}</td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $unit->parent?->parent_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $unit->location }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ $unit->status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                                            {{ $unit->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-slate-500">No units yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>