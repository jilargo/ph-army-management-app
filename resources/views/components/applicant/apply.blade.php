<?php

use App\Models\EnlistmentApplication;
use App\Models\EnlistmentDocument;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.auth')] class extends Component
{
    use WithFileUploads;

    public string $first_name = '';

    public string $middle_name = '';

    public string $last_name = '';

    public string $suffix = '';

    public string $date_of_birth = '';

    public string $gender = '';

    public string $contact_number = '';

    public string $personal_email = '';

    public string $address = '';

    public array $documents = [];

    protected function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:Male,Female',
            'contact_number' => 'required|string|max:20',
            'personal_email' => 'required|email|max:255',
            'address' => 'required|string|max:1000',
            'documents' => 'required|array|min:1|max:5',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function submit()
    {
        $validatedData = $this->validate();

        $application = EnlistmentApplication::create([
            'first_name' => $validatedData['first_name'],
            'middle_name' => $validatedData['middle_name'] ?? null,
            'last_name' => $validatedData['last_name'],
            'suffix' => $validatedData['suffix'] ?? null,
            'date_of_birth' => $validatedData['date_of_birth'],
            'gender' => $validatedData['gender'],
            'contact_number' => $validatedData['contact_number'],
            'personal_email' => $validatedData['personal_email'],
            'address' => $validatedData['address'],
            'status' => 'pending',
        ]);

        foreach ($this->documents as $document) {
            $filePath = $document->storeAs(
                'enlistment-documents/'.$application->id,
                Str::uuid().'.'.$document->getClientOriginalExtension(),
                'public'
            );

            EnlistmentDocument::create([
                'application_id' => $application->id,
                'file_path' => $filePath,
                'original_name' => $document->getClientOriginalName(),
                'mime_type' => $document->getClientMimeType(),
            ]);
        }

        $this->reset([
            'first_name',
            'middle_name',
            'last_name',
            'suffix',
            'date_of_birth',
            'gender',
            'contact_number',
            'personal_email',
            'address',
            'documents',
        ]);
        $this->resetValidation();

        session()->flash('status', 'Application submitted successfully. The recruitment office will review your requirements.');
    }

    public function removeDocument(int $index): void
    {
        unset($this->documents[$index]);
        $this->documents = array_values($this->documents);
    }
};
?>

<div>
    {{-- Public recruitment header --}}
    <div class="min-h-screen bg-slate-100 py-12 px-4">
        <div class="max-w-3xl mx-auto">

            <div class="text-center mb-8">
                <p class="text-sm uppercase tracking-widest text-emerald-700 font-semibold">
                    Republic of the Philippines &bull; Department of National Defense
                </p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">
                    Philippine Army Recruitment
                </h1>
                <p class="mt-2 text-sm text-slate-500 max-w-xl mx-auto">
                    Apply to join the Philippine Army. Submit your personal information and upload the
                    required documents (PDF or clear photos) — the recruitment office will review your application.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form wire:submit="submit" class="space-y-6" enctype="multipart/form-data">

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                    <div class="px-6 py-5 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">Personal Information</h2>
                        <p class="text-sm text-slate-500 mt-1">Basic identification required for enlistment.</p>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" wire:model="first_name" placeholder="Juan"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            @error('first_name') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                            <input type="text" wire:model="middle_name" placeholder="Santos"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" wire:model="last_name" placeholder="Dela Cruz"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            @error('last_name') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Suffix</label>
                            <select wire:model="suffix"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                                <option value="">None</option>
                                <option value="Jr.">Jr.</option>
                                <option value="Sr.">Sr.</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <input type="date" wire:model="date_of_birth"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            @error('date_of_birth') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                            <select wire:model="gender"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                                <option value="">Select sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            @error('gender') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                    <div class="px-6 py-5 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">Contact Information</h2>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number</label>
                            <input type="text" wire:model="contact_number" placeholder="09XX XXX XXXX"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            @error('contact_number') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" wire:model="personal_email" placeholder="you@example.com"
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            @error('personal_email') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Complete Address</label>
                            <textarea wire:model="address" rows="3" placeholder="Complete residential address"
                                      class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition"></textarea>
                            @error('address') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                    <div class="px-6 py-5 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">Requirements</h2>
                        <p class="text-sm text-slate-500 mt-1">
                            Upload your requirements as PDF or picture (1-5 files). e.g. birth certificate, diploma, NBI clearance.
                        </p>
                    </div>

                    <div class="p-6">

                        <input
                            type="file"
                            wire:model="documents"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        @error('documents') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        @error('documents.*') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror

                        <div wire:loading wire:target="documents" class="mt-3 text-sm text-indigo-600">
                            Uploading files...
                        </div>

                        @if ($documents)
                            <ul class="mt-4 space-y-2">
                                @foreach ($documents as $index => $document)
                                    <li wire:key="doc-{{ $index }}" class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                                        <span class="flex items-center gap-2 text-slate-700 truncate">
                                            @if (in_array($document->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']))
                                                <img src="{{ $document->temporaryUrl() }}" alt="" class="h-8 w-8 rounded object-cover">
                                            @else
                                                <span class="text-rose-500">📄</span>
                                            @endif
                                            <span class="truncate">{{ $document->getClientOriginalName() }}</span>
                                        </span>
                                        <button type="button" wire:click="removeDocument({{ $index }})"
                                                class="text-slate-400 hover:text-red-600 shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                        class="w-full bg-slate-900 text-white px-6 py-3.5 rounded-xl text-sm font-semibold hover:bg-slate-700 transition">
                    <span wire:loading.remove>Submit Application</span>
                    <span wire:loading>Submitting...</span>
                </button>

            </form>

        </div>
    </div>
</div>