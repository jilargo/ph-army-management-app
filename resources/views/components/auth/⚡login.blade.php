<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

new #[Layout('layouts.auth')] class extends Component
{
    public string $email = '';
    public string $password = '';

    public function login()
    {
        $validated = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ])) {

            session()->regenerate();

            if (Auth::user()->role !== 'admin') {
                $this->redirectRoute('user-dashboard');
                return;
            }

            $this->redirectRoute('personnel.index');
            return;
        }

        $this->addError('email', 'Email and Password are incorrect.');
    }
};
?>

<div class="min-h-screen bg-slate-950 flex items-center justify-center px-4 ">


    <div class="min-h-screen bg-slate-950">

        <div class="min-h-screen lg:grid lg:grid-cols-2">

            {{-- LEFT SIDE --}}
            <div class="relative hidden h-screen overflow-hidden lg:block">

                <img
                    src="https://images.unsplash.com/photo-1552109284-4e93add0367d?auto=format&fit=crop&fm=jpg&q=80&w=3000"
                    alt="Military personnel"
                    class="absolute inset-0 h-full w-full object-cover">

                <div class="absolute inset-0 bg-slate-950/70"></div>

                <div class="relative z-10 flex h-full flex-col justify-between p-12">

                    {{-- Branding --}}
                    <div class="flex items-center gap-4">

                        

                        

                    </div>


                    {{-- Main Content --}}
                    <div class="max-w-lg">

                        <p class="mb-4 text-sm font-semibold uppercase tracking-widest text-indigo-400">
                            Personnel Management
                        </p>

                        <h1 class="text-4xl font-bold leading-tight text-white xl:text-5xl">
                            Army Reservist
                            <span class="block text-slate-300">
                                Management System
                            </span>
                        </h1>

                        <p class="mt-6 text-base leading-7 text-slate-300">
                            Securely manage personnel, ranks, units, assignments,
                            and administrative operations in one centralized system.
                        </p>

                    </div>


                    {{-- Footer --}}
                    <p class="text-xs text-slate-400">
                        © {{ date('Y') }} PH Army Management System
                    </p>

                </div>

            </div>


            {{-- RIGHT SIDE --}}
            <div class="flex min-h-screen items-center justify-center px-6 py-12">

                <div class="w-full max-w-md">

                    {{-- Mobile Branding --}}
                    <div class="mb-8 text-center lg:hidden">

                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600">

                            <svg
                                class="h-7 w-7 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002-2zm10-10V7a4 4 0 00-8 0v2h8z" />
                            </svg>

                        </div>

                        <h1 class="text-xl font-bold text-white">
                            PH Army Management System
                        </h1>

                    </div>


                    {{-- Login Header --}}
                    <div class="mb-8">

                        <p class="mb-2 text-sm font-medium text-indigo-400">
                            Welcome back
                        </p>

                        <h1 class="text-3xl font-bold text-white">
                            Login
                        </h1>

                        <p class="mt-2 text-sm text-slate-400">
                            Sign in to access your account.
                        </p>

                    </div>


                    {{-- Login Card --}}
                    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-8 shadow-2xl">

                        {{-- IMPORTANT:
                         Your Livewire form is unchanged.
                    --}}
                        <form wire:submit="login" class="space-y-5">
@csrf
                            {{-- Email --}}
                            <div>

                                <label
                                    for="email"
                                    class="mb-2 block text-sm font-medium text-slate-200">
                                    Email Address
                                </label>

                                <input
                                    wire:model="email"
                                    type="email"
                                    id="email"
                                    placeholder="you@example.com"
                                    autocomplete="email"
                                    value="{{ old('email') }}"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">

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
                                    class="mb-2 block text-sm font-medium text-slate-200">
                                    Password
                                </label>

                                <input
                                    wire:model="password"
                                    type="password"
                                    id="password"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">

                                @error('password')
                                <p class="mt-2 text-sm text-red-400">
                                    {{ $message }}
                                </p>
                                @enderror

                            </div>


                            {{-- Login Button --}}
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:cursor-not-allowed disabled:opacity-60">

                                <span wire:loading.remove wire:target="login">
                                    Login
                                </span>

                                <span wire:loading wire:target="login">
                                    Logging in ....
                                </span>

                            </button>

                        </form>

                    </div>


                    {{-- Security Notice --}}
                    <div class="mt-6 rounded-xl border border-slate-800 bg-slate-900/50 p-4">

                        <p class="text-center text-xs leading-5 text-slate-400">
                            Authorized personnel only. System activities
                            may be monitored and logged.
                        </p>

                    </div>


                    {{-- Footer --}}
                    <p class="mt-6 text-center text-xs text-slate-600">
                        By: James Ian Largo
                    </p>

                </div>

            </div>

        </div>

    </div>
</div>