<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="overflow-hidden bg-slate-50">

    @php
        $isAdmin = auth()->user()->role === 'admin';

        $canRecommend = $isAdmin || (int) (auth()->user()->personnel?->rank?->level ?? 0) >= 12;

        $groups = [];

        if (! $isAdmin) {
            $groups[] = [
                'label' => 'Overview',
                'items' => [
                    ['label' => 'Dashboard', 'icon' => '📊', 'url' => route('user-dashboard'), 'active' => request()->routeIs('user-dashboard')],
                ],
            ];

            $groups[] = [
                'label' => 'Leave',
                'items' => [
                    ['label' => 'File Leave', 'icon' => '📝', 'url' => route('leaves.create'), 'active' => request()->routeIs('leaves.create')],
                    ['label' => 'My Leave', 'icon' => '🌴', 'url' => route('user-dashboard'), 'active' => request()->routeIs('user-dashboard')],
                ],
            ];

            if ($canRecommend) {
                $groups[] = [
                    'label' => 'Promotions',
                    'items' => [
                        ['label' => 'Recommend Promotions', 'icon' => '⭐', 'url' => route('promotions.index'), 'active' => request()->routeIs('promotions.index')],
                    ],
                ];
            }
        } else {
            $groups[] = [
                'label' => 'Overview',
                'items' => [
                    
                    ['label' => 'Dashboard', 'icon' => '📊', 'url' => '/', 'active' => request()->is('/')],
                ],
            ];

            $groups[] = [
                'label' => 'Personnel',
                'items' => [
                    ['label' => 'Soldiers', 'icon' => '👨‍✈️', 'url' => '/personnel/index', 'active' => request()->is('personnel/index') || request()->routeIs('personnel.profile') || request()->routeIs('personnel.edit')],
                    ['label' => 'Add Soldier', 'icon' => '✚', 'url' => route('personnel.create'), 'active' => request()->routeIs('personnel.create')],
                    ['label' => 'Enlistment Applications', 'icon' => '📥', 'url' => route('enlistments.index'), 'active' => request()->routeIs('enlistments.index')],
                    ['label' => 'Units', 'icon' => '🏢', 'url' => route('units.index'), 'active' => request()->routeIs('units.index')],
                ],
            ];

            $groups[] = [
                'label' => 'Management',
                'items' => [
                    
                    ['label' => 'Tasks', 'icon' => '📅', 'url' => route('tasks'), 'active' => request()->routeIs('tasks')],
                    ['label' => 'Leave Review', 'icon' => '🛫', 'url' => route('leaves.index'), 'active' => request()->routeIs('leaves.index')],
                    ['label' => 'Promotions', 'icon' => '🏅', 'url' => route('promotions.index'), 'active' => request()->routeIs('promotions.index')],
                    ['label' => 'Reports', 'icon' => '📈', 'url' => route('reports.index'), 'active' => request()->routeIs('reports.index')],
                    
                ],
            ];

            
        }
    @endphp

    <div
        x-data="{
            open: false,
            collapsed: (() => { try { return localStorage.getItem('sidebar-collapsed') === '1'; } catch (e) { return false; } })(),
            toggleCollapsed() {
                this.collapsed = !this.collapsed;
                try { localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0'); } catch (e) {}
            }
        }"
        class="flex h-screen supports-[height:100dvh]:h-dvh overflow-hidden"
    >

        {{-- ========================================================= --}}
        {{-- SIDEBAR --}}
        {{-- ========================================================= --}}

        <aside
            id="sidebar"
            aria-label="Sidebar"
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 text-white transition-all duration-200 ease-in-out
                   -translate-x-full lg:translate-x-0 lg:static lg:z-auto"
            :class="[open ? 'translate-x-0' : '', collapsed ? 'lg:w-20' : 'lg:w-64']"
        >

            {{-- Logo --}}
            <div class="flex h-16 shrink-0 items-center justify-between gap-3 px-6"
                 :class="collapsed ? 'lg:justify-center lg:px-0' : ''">

                <div class="flex min-w-0 items-center gap-3" :class="collapsed ? 'lg:justify-center' : ''">
                    <span class="shrink-0 text-2xl">🎖️</span>
                    <span class="min-w-0" :class="collapsed ? 'lg:hidden' : ''">
                        <span class="block truncate text-lg font-bold">Army Reserve</span>
                        <span class="block truncate text-xs text-slate-400">Management System</span>
                    </span>
                </div>

                {{-- Close drawer (mobile) --}}
                <button
                    type="button"
                    @click="open = false"
                    class="shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white lg:hidden"
                    aria-label="Close sidebar"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-6 overflow-y-auto overflow-x-hidden px-4 py-6" :class="collapsed ? 'lg:px-2' : ''">

                @foreach ($groups as $group)

                    <div class="space-y-1">

                        <p :class="collapsed ? 'lg:hidden' : ''"
                           class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ $group['label'] }}
                        </p>

                        @foreach ($group['items'] as $item)

                            <a
                                href="{{ $item['url'] }}"
                                :title="collapsed ? '{{ $item['label'] }}' : null"
                                :aria-label="collapsed ? '{{ $item['label'] }}' : null"
                                :class="collapsed ? 'lg:justify-center' : ''"
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400
                                       {{ $item['active'] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            >
                                <span class="shrink-0 text-lg">{{ $item['icon'] }}</span>
                                <span :class="collapsed ? 'lg:hidden' : ''" class="truncate">{{ $item['label'] }}</span>
                            </a>

                        @endforeach

                    </div>

                @endforeach

            </nav>

            {{-- Bottom --}}
            <div class="shrink-0 space-y-1 px-4 py-4" :class="collapsed ? 'lg:px-2' : ''">

                <p :class="collapsed ? 'lg:hidden' : ''"
                   class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500"
                   aria-hidden="true">
                    Account
                </p>

                

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        :title="collapsed ? 'Logout' : null"
                        :aria-label="collapsed ? 'Logout' : null"
                        :class="collapsed ? 'lg:justify-center' : ''"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-red-400 transition hover:bg-slate-800 hover:text-red-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400"
                    >
                        <span class="shrink-0 text-lg">🔓</span>
                        <span :class="collapsed ? 'lg:hidden' : ''">Logout</span>
                    </button>
                </form>

            </div>

        </aside>

        {{-- Mobile overlay --}}
        <div
            x-show="open"
            x-cloak
            @click="open = false"
            class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden"
        ></div>

        {{-- ========================================================= --}}
        {{-- MAIN AREA --}}
        {{-- ========================================================= --}}

        <div class="relative flex min-w-0 flex-1 flex-col">

            {{-- Header --}}
            <header
                class="flex h-16 shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">

                    {{-- Hamburger (mobile) --}}
                    <button
                        type="button"
                        @click="open = true"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 lg:hidden"
                        aria-label="Open sidebar"
                        aria-controls="sidebar"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    {{-- Collapse toggle (desktop) --}}
                    <button
                        type="button"
                        @click="toggleCollapsed()"
                        class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 lg:inline-flex"
                        aria-label="Toggle sidebar"
                        aria-controls="sidebar"
                        :aria-expanded="String(!collapsed)"
                    >
                        <svg class="h-5 w-5 transition-transform duration-200" :class="collapsed ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-semibold text-slate-900 sm:text-xl">
                            {{ $heading ?? 'LAANG KAWAL' }}
                        </h2>
                        <p class="hidden truncate text-sm text-slate-500 sm:block">
                            Philippine Army Reserve Management System
                        </p>
                    </div>

                </div>

                {{-- User --}}
                <div class="flex shrink-0 items-center gap-3">

                    <livewire:notification-bell />

                    <div class="hidden text-right md:block">
                        <p class="text-sm font-semibold">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ ucfirst(Auth::user()->role) }}
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-800 text-white font-semibold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>

                </div>
            </header>

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto overflow-x-hidden overscroll-contain bg-slate-50">

                <div class="mx-auto w-full max-w-7xl p-4 sm:p-6 lg:p-8">

                    {{-- Flash Message --}}
                    @if (session()->has('status'))

                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-init="setTimeout(() => show = false, 4000)"
                        x-transition
                        class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-800">
                        {{ session('status') }}
                    </div>

                    @endif

                    {{-- Page Content --}}
                    <div class="min-w-0 w-full">
                        {{ $slot }}
                    </div>

                </div>

            </main>

        </div>

    </div>

    @livewireScripts

</body>

</html>