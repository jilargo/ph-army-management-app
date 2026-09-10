<?php

use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Units;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public ?int $personnel_id;

    public string $first_name;

    public string $middle_name;

    public string $last_name;

    public ?string $suffix = '';

    public string $date_of_birth = '';

    public string $status = '';

    public ?string $gender = '';

    public string $date_of_entry = '';

    public string $personal_email = '';

    public string $contact_number = '';

    public string $address = '';

    public $profile_picture = null;

    // Linked login account management
    public ?string $account_email = null;

    public bool $has_account = false;

    public string $new_password = '';

    public string $new_password_confirmation = '';

    // rank_id is use to see the ranks table to browse for rank name to be use if the user wants to edit the rank of the personnel
    public ?int $rank_id = null;

    // ranks array to hold the values
    public $Ranks = [];

    // unit of the personnel
    public ?int $unit_id = null;

    // units array to hold the values
    public $Units = [];

    #[Computed]
    public function personnel(): Personnel
    {
        return Personnel::findOrFail($this->personnel_id);
    }

    public function mount(Personnel $personnel)
    {
        $this->personnel_id = $personnel->personnel_id;
        $this->first_name = $personnel->first_name ?? '';
        $this->middle_name = $personnel->middle_name ?? '';
        $this->last_name = $personnel->last_name ?? '';
        $this->suffix = $personnel->suffix ?? '';
        $this->date_of_birth = $personnel->date_of_birth ?? '';
        $this->gender = $personnel->gender ?? '';
        $this->contact_number = $personnel->contact_number ?? '';
        $this->personal_email = $personnel->personal_email ?? '';
        $this->address = $personnel->address ?? '';
        $this->date_of_entry = $personnel->date_of_entry ?? '';
        $this->status = $personnel->status ?? '';
        // Get the current rank ID
        $this->rank_id = $personnel->rank_id;

        // Get the current unit ID
        $this->unit_id = $personnel->unit_id;

        // Load linked account info if one exists
        if ($personnel->user) {
            $this->has_account = true;
            $this->account_email = $personnel->user->email;
        }

        // Get all ranks for the dropdown
        $this->Ranks = Ranks::all();

        // Get all units for the dropdown
        $this->Units = Units::all();

        // dd($personnel->toArray());
    }

    // for updating personnel records
    public function update_personnel()
    {
        $validatedData = $this->validate([
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'suffix' => 'nullable',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'status' => 'required',
            'rank_id' => 'required|integer|exists:ranks,rank_id',
            'unit_id' => 'required|integer|exists:units,unit_id',
            'date_of_entry' => 'required',
            'personal_email' => 'required',
            'contact_number' => 'required',
            'address' => 'required',
            'profile_picture' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $personnel = Personnel::findOrFail($this->personnel_id);

        $oldPicture = $personnel->getRawOriginal('profile_picture');

        if ($this->profile_picture) {
            $validatedData['profile_picture'] = $this->profile_picture->store('personnel', 'public');
        }

        $personnel->update($validatedData);

        if ($this->profile_picture && $oldPicture) {
            Storage::disk('public')->delete($oldPicture);
        }

        // If the account email was changed, update the linked user's email too
        if ($personnel->user && $this->account_email && $personnel->user->email !== $this->account_email) {
            $this->validate([
                'account_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($personnel->user->id)],
            ]);
            $personnel->user->update(['email' => $this->account_email]);
        }

        // Optionally reset the password
        if ($personnel->user && ! empty($this->new_password)) {
            $this->validate([
                'new_password' => ['required', 'min:8', 'confirmed'],
            ]);
            $personnel->user->update(['password' => Hash::make($this->new_password)]);
        }

        $this->resetPasswordFields();

        session()->flash('status', 'Personnel Successfully Saved');
        $this->redirectRoute('personnel.index');
    }

    public function createLinkAccount()
    {
        $this->validate([
            'account_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $personnel = Personnel::findOrFail($this->personnel_id);

        $user = User::create([
            'name' => trim($this->first_name.' '.$this->middle_name.' '.$this->last_name),
            'email' => $this->account_email,
            'password' => $this->new_password,
            'role' => 'user',
        ]);

        $personnel->update(['user_id' => $user->id]);

        $this->has_account = true;
        $this->resetPasswordFields();
        $this->resetValidation();
        session()->flash('status', 'Login account created and linked to this soldier.');
    }

    protected function resetPasswordFields()
    {
        $this->new_password = '';
        $this->new_password_confirmation = '';
    }
};

?>

<div class="mt-2">
    <div class="max-w-5xl mx-auto">

        {{-- Page Header --}}
        <div class="mb-2">
            <h1 class="text-2xl  font-bold text-slate-900">
                Update Personnel
            </h1>
            <p class="mt-1 mb-2 text-sm text-gray-500">
                Update a new personnel record in the Philippine Army Management System.
            </p>
        </div>
        {{-- Form --}}

        <form class="space-y-8" wire:submit="update_personnel">
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
                                wire:model.live="first_name"
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
                                for="unit"
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

                                <option value="">Select unit</option>

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
                        Optional. Uploading a new photo will replace the current one.
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
                                <x-avatar :personnel="$this->personnel" size="lg" class="border-4 border-slate-100"/>
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


            {{-- Login Account --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">

                <div class="px-6 py-5 border-b border-gray-200">

                    <h2 class="text-lg font-semibold text-slate-900">
                        Login Account
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ $has_account ? 'Manage the login credentials for this soldier.' : 'This soldier currently has no login account. Create one so they can log in and file leave requests.' }}
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

                            @if ($has_account)
                                <input
                                    type="email"
                                    id="account_email"
                                    name="account_email"
                                    wire:model="account_email"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                       focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                       outline-none transition">
                            @else
                                <input
                                    type="email"
                                    id="account_email"
                                    name="account_email"
                                    wire:model="account_email"
                                    placeholder="soldier@army.ph"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                       focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                       outline-none transition">
                            @endif
                            @error('account_email')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Password --}}
                        <div>
                            <label
                                for="new_password"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $has_account ? 'Reset Password (optional)' : 'Password' }}
                            </label>

                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                wire:model="new_password"
                                placeholder="Minimum 8 characters"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                            @error('new_password')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- Confirm Password --}}
                        <div class="md:col-span-2">
                            <label
                                for="new_password_confirmation"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                id="new_password_confirmation"
                                name="new_password_confirmation"
                                wire:model="new_password_confirmation"
                                placeholder="Re-type the password"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                   focus:border-slate-500 focus:ring-2 focus:ring-slate-200
                                   outline-none transition">
                        </div>

                        @if (! $has_account)
                            <div class="md:col-span-2">
                                <button
                                    type="button"
                                    wire:click="createLinkAccount"
                                    class="px-5 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-medium
                                       hover:bg-slate-700 transition">
                                    Create Login Account
                                </button>
                            </div>
                        @endif

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
                    Update
                </button>

            </div>

        </form>

    </div>
</div>