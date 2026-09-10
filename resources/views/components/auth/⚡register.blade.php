<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(){
        $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
    ]);
    $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);
        
        Auth::login($user);
        session()->regenerate();
        $this->redirectRoute('user-dashboard');
    }
    
};
?>

<div class="min-h-screen bg-slate-950 flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-md">

        {{-- Logo / Branding --}}
        <div class="text-center mb-8">

            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-600/30">
                <svg
                    class="h-7 w-7 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v2h8z"
                    />
                </svg>
            </div>

            <h1 class="text-3xl font-bold tracking-tight text-white">
                Create your account
            </h1>

            <p class="mt-2 text-sm text-slate-400">
                Join the PH Army Management System
            </p>

        </div>


        {{-- Registration Card --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-8 shadow-2xl">

            <form wire:submit="register" class="space-y-5">

                {{-- Name --}}
                <div>
                    <label
                        for="name"
                        class="mb-2 block text-sm font-medium text-slate-200"
                    >
                        Full Name
                    </label>

                    <input
                        wire:model="name"
                        type="text"
                        id="name"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        value=" {{old('name')}} "
                        class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    >

                    @error('name')
                        <p class="mt-2 text-sm text-red-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- Email --}}
                <div>
                    <label
                        for="email"
                        class="mb-2 block text-sm font-medium text-slate-200"
                    >
                        Email Address
                    </label>

                    <input
                        wire:model="email"
                        type="email"
                        id="email"
                        placeholder="you@example.com"
                        autocomplete="email"
                        value=" {{old('email')}} "
                        class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    >

                    @error('email')
                        <p class="mt-2 text-sm text-red-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- Password --}}
                <div>
                    <label
                        for="password"
                        class="mb-2 block text-sm font-medium text-slate-200"
                    >
                        Password
                    </label>

                    <input
                        wire:model="password"
                        type="password"
                        id="password"
                        placeholder="Create a password"
                        autocomplete="new-password"
                        
                        class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    >

                    @error('password')
                        <p class="mt-2 text-sm text-red-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- Confirm Password --}}
                <div>
                    <label
                        for="password_confirmation"
                        class="mb-2 block text-sm font-medium text-slate-200"
                    >
                        Confirm Password
                    </label>

                    <input
                        wire:model="password_confirmation"
                        type="password"
                        id="password_confirmation"
                        placeholder="Confirm your password"
                        autocomplete="new-password"
                        
                        class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    >
                </div>
                {{-- Submit --}}
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:cursor-not-allowed disabled:opacity-60"
                >

                    <span wire:loading.remove wire:target="register">
                        Create Account
                    </span>

                    <span wire:loading wire:target="register">
                        Creating Account...
                    </span>

                </button>

            </form>


            {{-- Login Link --}}
            <div class="mt-6 border-t border-slate-800 pt-6 text-center">

                <p class="text-sm text-slate-400">
                    Already have an account?

                    <a
                        href="{{ route('login') }}"
                        class="font-medium text-indigo-400 transition hover:text-indigo-300"
                    >
                        Sign in
                    </a>
                </p>

            </div>

        </div>


        {{-- Footer --}}
        <p class="mt-6 text-center text-xs text-slate-500">
            PH Army Management System
        </p>

    </div>

</div>