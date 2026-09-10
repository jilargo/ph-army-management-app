<?php

use App\Models\Leaves;
use App\Models\Personnel;
use App\Models\Promotions;
use App\Models\Units;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function totalPersonnel()
    {
        return Personnel::count();
    }

    #[Computed]
    public function activeSoldiers()
    {
        return Personnel::whereRaw('LOWER(status) = ?', ['active'])->count();
    }

    #[Computed]
    public function activeUnits()
    {
        return Units::count();
    }

    #[Computed]
    public function pendingLeaves()
    {
        return Leaves::where('status', 'pending')->count();
    }

    #[Computed]
    public function pendingPromotions()
    {
        return Promotions::where('status', 'pending')->count();
    }
};
?>

<div>
    <div class="max-w-5xl mx-auto mt-2">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">
                Reports
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Generate polished PDF reports for briefings, reviews, and presentations.
            </p>
        </div>

        {{-- Current snapshot --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-sm text-gray-500">Total Personnel</p>
                <h3 class="text-3xl font-bold mt-1">{{ number_format($this->totalPersonnel) }}</h3>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 border-l-4 border-l-green-500 p-5">
                <p class="text-sm text-gray-500">Active Soldiers</p>
                <h3 class="text-3xl font-bold mt-1 text-green-600">{{ number_format($this->activeSoldiers) }}</h3>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-sm text-gray-500">Active Units</p>
                <h3 class="text-3xl font-bold mt-1">{{ number_format($this->activeUnits) }}</h3>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-sm text-gray-500">Pending Leaves</p>
                <h3 class="text-3xl font-bold mt-1">{{ number_format($this->pendingLeaves) }}</h3>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-sm text-gray-500">Pending Promotions</p>
                <h3 class="text-3xl font-bold mt-1">{{ number_format($this->pendingPromotions) }}</h3>
            </div>

        </div>

        {{-- Download card --}}
        <div class="bg-gradient-to-br from-slate-900 to-emerald-900 rounded-2xl p-8 text-white shadow-sm">

            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">

                <div>
                    <h2 class="text-xl font-semibold">
                        Personnel Strength Report
                    </h2>
                    <p class="text-sm text-slate-300 mt-1 max-w-lg">
                        A presentation-ready document with headline statistics, status and rank breakdowns,
                        and the full personnel roster — generated as a PDF.
                    </p>
                </div>

                <a href="{{ route('reports.personnel') }}"
                   class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-slate-900 px-5 py-3 rounded-lg text-sm font-semibold transition shrink-0">
                    <span aria-hidden="true">📄</span>
                    Download PDF Report
                </a>

            </div>

        </div>

    </div>
</div>