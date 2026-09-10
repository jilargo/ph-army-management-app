<?php

namespace App\Http\Controllers;

use App\Models\EnlistmentApplication;
use App\Models\Leaves;
use App\Models\Personnel;
use App\Models\Promotions;
use App\Models\Task;
use App\Models\Units;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function personnel()
    {
        $data = [
            'totalPersonnel' => Personnel::count(),
            'activeSoldiers' => Personnel::whereRaw('LOWER(status) = ?', ['active'])->count(),
            'inactiveSoldiers' => Personnel::whereRaw('LOWER(status) = ?', ['inactive'])->count(),
            'retiredSoldiers' => Personnel::whereRaw('LOWER(status) = ?', ['retired'])->count(),
            'onLeaveSoldiers' => Personnel::whereRaw('LOWER(status) = ?', ['leave'])->count(),
            'newlyAccepted' => Personnel::where('created_at', '>=', now()->subDays(30))->count(),
            'activeUnits' => Units::count(),
            'pendingLeaves' => Leaves::where('status', 'pending')->count(),
            'pendingPromotions' => Promotions::where('status', 'pending')->count(),
            'openTasks' => Task::where('status', '!=', 'completed')->count(),
            'completedTasks' => Task::where('status', 'completed')->count(),
            'pendingApplications' => EnlistmentApplication::where('status', 'pending')->count(),
            'acceptedApplications' => EnlistmentApplication::where('status', 'accepted')->count(),
            'rejectedApplications' => EnlistmentApplication::where('status', 'rejected')->count(),
            'rankDistribution' => Personnel::with('rank')
                ->get()
                ->groupBy(fn (Personnel $personnel) => $personnel->rank?->rank_name ?? 'Unassigned')
                ->map->count()
                ->sortDesc()
                ->map(fn ($count, $rank) => ['rank' => $rank, 'total' => $count])
                ->values(),
            'newlyAcceptedList' => Personnel::with('rank', 'units')
                ->where('created_at', '>=', now()->subDays(30))
                ->orderByDesc('created_at')
                ->get(),
            'tasks' => Task::with('personnel')
                ->orderByRaw("CASE WHEN status = 'completed' THEN 0 ELSE 1 END")
                ->orderByDesc('due_date')
                ->get(),
            'enlistments' => EnlistmentApplication::withCount('documents')
                ->orderByDesc('created_at')
                ->get(),
            'personnel' => Personnel::with('rank', 'units')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
            'generatedBy' => auth()->user()->name,
            'generatedAt' => now(),
        ];

        return $this->streamPdf('Personnel_Report', 'pdf.personnel-report', $data);
    }

    protected function streamPdf(string $filename, string $view, array $data)
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename.'_'.now()->format('Y-m-d').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
