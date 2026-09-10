<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Units;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public $Ranks = [];
    public $Units = [];
    public ?int $rank_id = null;
    public ?int $unit_id = null;
    public string $first_name = '';
    public string $middle_name = '';
    public string $last_name = '';
    public ?string $suffix = null;
    public string $date_of_birth = '';

    public string $gender = '';
    public string $date_of_entry = '';
    public string $personal_email = '';
    public string $contact_number = '';
    public string $address = '';
    public $profile_picture = null;

    // Login account credentials (optional) used to create a linked User for the soldier
    public string $account_email = '';
    public string $account_password = '';
    public string $account_password_confirmation = '';


    public function mount()
    {
        $this->Ranks = Ranks::all();
        $this->Units = Units::all();
    }


    public function save_personnel()
    {
        $validatedData = $this->validate([
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'suffix' => 'nullable',
            'date_of_birth' => 'required',
            'gender' => 'required',

            'rank_id' => 'required|integer|exists:ranks,rank_id',
            'unit_id' => 'required|integer|exists:units,unit_id',
            'date_of_entry' => 'required',
            'personal_email' => 'required',
            'contact_number' => 'required',
            'address' => 'required',
            'account_email' => 'nullable|email|max:255|unique:users,email',
            'account_password' => 'required_with:account_email|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $personnelData = $validatedData;

        if ($this->profile_picture) {
            $personnelData['profile_picture'] = $this->profile_picture->store('personnel', 'public');
        } else {
            unset($personnelData['profile_picture']);
        }

        // Create a login account for this soldier if an account email was provided
        if (! empty($validatedData['account_email'])) {
            $user = User::create([
                'name' => trim($validatedData['first_name'] . ' ' . ($validatedData['middle_name'] ?? '') . ' ' . $validatedData['last_name']),
                'email' => $validatedData['account_email'],
                'password' => $validatedData['account_password'],
                'role' => 'user',
            ]);

            $personnelData['user_id'] = $user->id;
        }

        unset($personnelData['account_email'], $personnelData['account_password'], $personnelData['account_password_confirmation']);

        Personnel::create($personnelData);
        session()->flash('status', 'Personnel Successfully Saved');
        $this->redirect('/personnel/index');
    }
};
?>

<div>
    <div class="max-w-5xl mx-auto">

        {{-- Page Header --}}
        <div class="mb-2">
            <h1 class="text-2xl font-bold text-slate-900">
                Add Personnel
            </h1>
            <p class="mt-1 mb-2 text-sm text-gray-500">
                Register a new personnel record in the Philippine Army Management System.
            </p>
        </div>
        {{-- Form --}}

        <form class="space-y-8" wire:submit="save_personnel">
            @csrf
            {{-- Personal Information --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

                <div class="px-6 py-5 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Personal Information
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Basic identification information of the personnel.
                    </p>
                </div>


                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- First Name --}}
                        <div>
                            <label
                                for="first_name"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                First Name
                            </label>

                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                wire:model="first_name"
                                placeholder="Juan"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('first_name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>




                        {{-- Middle Name --}}
                        <div>
                            <label
                                for="middle_name"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Middle Name
                            </label>

                            <input
                                type="text"
                                id="middle_name"
                                name="middle_name"
                                wire:model="middle_name"
                                placeholder="Santos"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('middle_name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Last Name --}}
                        <div>
                            <label
                                for="last_name"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Last Name
                            </label>

                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                wire:model="last_name"
                                placeholder="Dela Cruz"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('last_name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Suffix --}}
                        <div>
                            <label
                                for="suffix"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Suffix
                            </label>

                            <select
                                id="suffix"
                                name="suffix"
                                wire:model="suffix"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                                <option value="">None</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                            </select>
                        </div>


                        {{-- Date of Birth --}}
                        <div>
                            <label
                                for="date_of_birth"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Date of Birth
                            </label>

                            <input
                                type="date"
                                id="date_of_birth"
                                name="date_of_birth"
                                wire:model="date_of_birth"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('date_of_birth')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Sex --}}
                        <div>
                            <label
                                for="gender"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Gender
                            </label>

                            <select
                                id="gender"
                                name="gender"
                                wire:model="gender"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                                <option value="">Select sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            @error('gender')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                </div>

            </div>


            {{-- Military Information --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">




                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Rank --}}
                        <div>
                            <label
                                for="rank"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Rank
                            </label>

                            <select
                                id="rank"
                                name="rank"
                                wire:model="rank_id"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">

                                <option value="">Select rank</option>

                                @foreach ($Ranks as $rank)
                                <option value="{{ $rank->rank_id }}">
                                    {{ $rank->rank_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('rank_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Unit --}}
                        <div>
                            <label
                                for="rank"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Unit
                            </label>

                            <select
                                id="unit"
                                name="unit"
                                wire:model="unit_id"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">

                                <option value="">Select Unit</option>

                                @foreach ($Units as $unit)
                                <option value="{{ $unit->unit_id }}">
                                    {{ $unit->unit_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>







                        {{-- Status --}}
                        <div>
                            <label
                                for="status"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>

                            <select
                                id="status"
                                name="status"
                                wire:model="status"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                                <option value="Active">Active</option>
                                <option value="Leave">On Leave</option>
                                <option value="Retired">Retired</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                            @error('status')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Enlistment Date --}}
                        <div>
                            <label
                                for="enlistment_date"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Date of Entry
                            </label>

                            <input
                                type="date"
                                id="date_of_entry"
                                name="date_of_entry"
                                wire:model="date_of_entry"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('date_of_entry')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>




                    </div>

                </div>

            </div>


            {{-- Contact Information --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

                <div class="px-6 py-5 border-b border-gray-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Contact Information
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Contact and residential information.
                    </p>

                </div>


                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Email --}}
                        <div>
                            <label
                                for="email"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="personal_email"
                                name="personal_email"
                                wire:model="personal_email"
                                placeholder="juan.delacruz@example.com"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('personal_email')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Phone --}}
                        <div>
                            <label
                                for="phone"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Contact Number
                            </label>

                            <input
                                type="text"
                                id="contact_number"
                                name="contact_number"
                                wire:model="contact_number"
                                placeholder="09XX XXX XXXX"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('contact_number')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Address --}}
                        <div class="md:col-span-2">

                            <label
                                for="address"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Address
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                wire:model="address"
                                placeholder="Complete residential address"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition"></textarea>
                            @error('address')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror

                        </div>

                    </div>

                </div>

            </div>


            {{-- Profile Picture --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

                <div class="px-6 py-5 border-b border-gray-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Profile Picture
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Optional. Upload a photo so the soldier is easily recognizable across the system.
                    </p>

                </div>

                <div class="p-6">

                    <div class="flex items-start gap-6">

                        <div class="shrink-0">
                            @if ($this->profile_picture?->isPreviewable())
                            <img
                                src="{{ $this->profile_picture->temporaryUrl() }}"
                                alt="Profile preview"
                                class="h-24 w-24 rounded-full object-cover border-4 border-slate-100">
                            @else
                            <div class="flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            @endif
                        </div>

                        <div class="flex-1">
                            <label
                                for="profile_picture"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Profile Picture
                            </label>

                            <input
                                type="file"
                                id="profile_picture"
                                wire:model="profile_picture"
                                accept="image/jpeg,image/png,image/webp"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">

                            <p class="mt-1 text-xs text-slate-400">
                                JPG, PNG or WEBP. Maximum size of 2 MB.
                            </p>

                            <div wire:loading wire:target="profile_picture" class="mt-2 text-sm text-indigo-600">
                                Uploading...
                            </div>

                            @error('profile_picture')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                </div>

            </div>


            {{-- Account Credentials --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

                <div class="px-6 py-5 border-b border-gray-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Login Account
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Optional. Create login credentials so this soldier can log in and file leave requests.
                    </p>

                </div>


                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Account Email --}}
                        <div>
                            <label
                                for="account_email"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Account Email
                            </label>

                            <input
                                type="email"
                                id="account_email"
                                name="account_email"
                                wire:model="account_email"
                                placeholder="soldier@army.ph"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('account_email')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Account Password --}}
                        <div>
                            <label
                                for="account_password"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>

                            <input
                                type="password"
                                id="account_password"
                                name="account_password"
                                wire:model="account_password"
                                placeholder="Minimum 8 characters"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('account_password')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Account Password Confirmation --}}
                        <div class="md:col-span-2">
                            <label
                                for="account_password_confirmation"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                id="account_password_confirmation"
                                name="account_password_confirmation"
                                wire:model="account_password_confirmation"
                                placeholder="Re-type the password"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                        </div>

                    </div>

                </div>

            </div>


            {{-- Form Actions --}}
            <div class="flex items-center justify-end gap-3">

                <a
                    href="/"
                    class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-medium
                       text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-medium
                       hover:bg-slate-700 transition">
                    Save Personnel
                </button>

            </div>

        </form>

    </div>
</div>