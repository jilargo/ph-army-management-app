<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Task;
use App\Models\Personnel;
use App\Notifications\TaskAssignedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

new #[Layout('layouts.app')] class extends Component
{
    public int $currentMonth;
    public int $currentYear;

    public ?int $viewingTaskId = null;
    public ?int $creatingTaskDate = null;
    public ?int $editingTaskId = null;
    public ?int $taskToDelete = null;

    public string $title = '';
    public string $description = '';
    public string $due_date = '';
    public ?string $start_time = null;
    public ?string $end_time = null;
    public string $priority = 'medium';
    public string $status = 'pending';
    public string $type = 'general';
    public ?int $personnel_id = null;
    public array $assignee_ids = [];
    public string $assignee_search = '';

    public function mount()
    {
        $this->currentMonth = (int) Carbon::now()->format('m');
        $this->currentYear = (int) Carbon::now()->format('Y');
    }

    #[Computed]
    public function personnels()
    {
        return Personnel::with('rank')
            ->orderBy('last_name')
            ->when($this->assignee_search, function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', "%{$this->assignee_search}%")
                        ->orWhere('last_name', 'like', "%{$this->assignee_search}%")
                        ->orWhereHas('rank', fn ($q) => $q->where('rank_name', 'like', "%{$this->assignee_search}%"));
                });
            })
            ->get();
    }

    #[Computed]
    public function tasks()
    {
        $start = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfWeek();
        $end = Carbon::create($this->currentYear, $this->currentMonth, 1)->endOfMonth()->endOfWeek();

        return Task::with('personnel')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn ($task) => Carbon::parse($task->due_date)->toDateString());
    }

    #[Computed]
    public function calendarDays()
    {
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $start = $firstDay->copy()->startOfWeek();
        $daysInMonth = $firstDay->daysInMonth;

        $cells = [];
        for ($i = 0; $i < 42; $i++) {
            $date = $start->copy()->addDays($i);
            $cells[] = [
                'date' => $date,
                'isCurrentMonth' => $date->month === $this->currentMonth && $date->year === $this->currentYear,
                'isToday' => $date->isToday(),
                'dayNumber' => $date->day,
            ];
        }

        return $cells;
    }

    public function goToPreviousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = (int) $date->format('m');
        $this->currentYear = (int) $date->format('Y');
    }

    public function goToNextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = (int) $date->format('m');
        $this->currentYear = (int) $date->format('Y');
    }

    public function goToToday()
    {
        $this->currentMonth = (int) Carbon::now()->format('m');
        $this->currentYear = (int) Carbon::now()->format('Y');
    }

    public function openCreateModal(?string $date = null)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $this->resetForm();
        $this->creatingTaskDate = 1;
        $this->due_date = $date ?? Carbon::now()->toDateString();
    }

    public function viewTask(int $taskId)
    {
        $this->viewingTaskId = $taskId;
    }

    public function editTask(int $taskId)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $task = Task::findOrFail($taskId);

        $this->editingTaskId = $task->task_id;
        $this->title = $task->title;
        $this->description = $task->description ?? '';
        $this->due_date = $task->due_date->toDateString();
        $this->start_time = $task->start_time ? Carbon::parse($task->start_time)->format('H:i') : null;
        $this->end_time = $task->end_time ? Carbon::parse($task->end_time)->format('H:i') : null;
        $this->priority = $task->priority;
        $this->status = $task->status;
        $this->type = $task->type;
        $this->personnel_id = $task->personnel_id;
        $this->assignee_ids = $task->assignees()->pluck('personnels.personnel_id')->map(fn ($id) => (int) $id)->all();

        $this->viewingTaskId = null;
        $this->creatingTaskDate = null;
    }

    public function confirmDelete(int $taskId)
    {
        $this->taskToDelete = $taskId;
    }

    public function deleteTask()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        Task::findOrFail($this->taskToDelete)->delete();
        $this->taskToDelete = null;
        session()->flash('status', 'Task successfully deleted.');
    }

    public function storeTask()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $validatedData = $this->validate($this->taskValidationRules(), $this->taskValidationMessages());

        $assigneeIds = array_values(array_map('intval', $this->assignee_ids ?? []));

        $validatedData['personnel_id'] = $assigneeIds[0] ?? $validatedData['personnel_id'] ?? null;
        $validatedData['user_id'] = auth()->id();

        $task = Task::create($validatedData);

        $this->syncAssignees($task, $assigneeIds);

        $mentionedIds = $this->parseMentions($task->title.'. '.($task->description ?? ''));
        $this->notifyAssignees($task, $assigneeIds, $mentionedIds);

        $this->resetForm();
        session()->flash('status', 'Task successfully created.');
    }

    public function updateTask()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $validatedData = $this->validate($this->taskValidationRules(), $this->taskValidationMessages());

        $task = Task::findOrFail($this->editingTaskId);

        $currentAssigneeIds = $task->assignees()->pluck('personnels.personnel_id')->map(fn ($id) => (int) $id)->all();
        $assigneeIds = array_values(array_map('intval', $this->assignee_ids ?? []));

        $task->update([
            ...$validatedData,
            'personnel_id' => $assigneeIds[0] ?? $validatedData['personnel_id'] ?? null,
        ]);

        $this->syncAssignees($task, $assigneeIds);

        $newAssigneeIds = array_values(array_diff($assigneeIds, $currentAssigneeIds));

        $mentionedIds = $this->parseMentions($validatedData['title'].'. '.($validatedData['description'] ?? ''));
        $this->notifyAssignees($task, $newAssigneeIds, $mentionedIds);

        $this->resetForm();
        session()->flash('status', 'Task successfully updated.');
    }

    /**
     * @return array<string, string>
     */
    private function taskValidationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'type' => 'required|in:general,operations,training,logistics,administrative',
            'personnel_id' => 'nullable|integer|exists:personnels,personnel_id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'integer|exists:personnels,personnel_id',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function taskValidationMessages(): array
    {
        return [
            'due_date.after_or_equal' => 'Due date cannot be in the past.',
        ];
    }

    private function syncAssignees(Task $task, array $assigneeIds): void
    {
        if ($assigneeIds) {
            $task->assignees()->syncWithPivotValues($assigneeIds, ['assigned_by' => auth()->id()]);
        } else {
            $task->assignees()->sync([]);
        }
    }

    /**
     * Extract @mentions from free text and map them to matching personnel ids.
     *
     * A mention matches a soldier when the token equals their last name or
     * appears as a contiguous part of their full name.
     *
     * @return array<int>
     */
    private function parseMentions(string $text): array
    {
        if (! preg_match_all('/(?<![\w@])@([\p{L}][\p{L}\-\']*)/u', $text, $matches)) {
            return [];
        }

        $personnels = Personnel::all();
        $mentionedIds = [];

        foreach (array_unique(array_map(static fn ($name) => mb_strtolower(trim($name)), $matches[1])) as $token) {
            foreach ($personnels as $personnel) {
                $lastName = mb_strtolower((string) $personnel->last_name);
                $fullName = mb_strtolower($personnel->full_name);

                if ($lastName === $token || str_contains(" $fullName ", " $token ")) {
                    $mentionedIds[] = (int) $personnel->personnel_id;
                }
            }
        }

        return array_values(array_unique($mentionedIds));
    }

    /**
     * Send one notification per affected soldier. Soldiers who are both
     * assigned and mentioned receive a single "assigned" notification.
     *
     * @param  array<int>  $assigneeIds
     * @param  array<int>  $mentionedIds
     */
    private function notifyAssignees(Task $task, array $assigneeIds, array $mentionedIds): void
    {
        $personnelIds = array_values(array_unique(array_merge($assigneeIds, $mentionedIds)));

        if (! $personnelIds) {
            return;
        }

        $personnels = Personnel::with('user')->whereIn('personnel_id', $personnelIds)->get();

        foreach ($personnels as $personnel) {
            $user = $personnel->user;

            if (! $user) {
                continue;
            }

            $assigned = in_array((int) $personnel->personnel_id, $assigneeIds, true);
            $mentioned = in_array((int) $personnel->personnel_id, $mentionedIds, true);

            Notification::send($user, new TaskAssignedNotification(
                $task,
                $personnel,
                $mentioned && ! $assigned,
            ));
        }
    }

    public function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->due_date = '';
        $this->start_time = null;
        $this->end_time = null;
        $this->priority = 'medium';
        $this->status = 'pending';
        $this->type = 'general';
        $this->personnel_id = null;
        $this->assignee_ids = [];
        $this->assignee_search = '';
        $this->editingTaskId = null;
        $this->creatingTaskDate = null;
        $this->viewingTaskId = null;
        $this->taskToDelete = null;
        $this->resetValidation();
    }

    public function updated($property)
    {
        if ($property === 'end_time' && $this->start_time && $this->end_time <= $this->start_time) {
            $this->end_time = null;
        }
    }
};
?>

<div>
    <div class="bg-slate-50">

        <div class="mx-auto max-w-7xl">

            {{-- Header --}}
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h1 class="text-2xl font-bold text-slate-900">
                        Calendar
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Manage your tasks and deadlines.
                    </p>
                </div>

                @if (auth()->user()->role === 'admin')
                <button
                    wire:click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>

                    New Task
                </button>
                @endif

            </div>

            {{-- Calendar Card --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                {{-- Calendar Toolbar --}}
                <div class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-3">

                        {{-- Previous --}}
                        <button
                            wire:click="goToPreviousMonth"
                            class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        {{-- Today --}}
                        <button
                            wire:click="goToToday"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Today
                        </button>

                        {{-- Next --}}
                        <button
                            wire:click="goToNextMonth"
                            class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                    </div>

                    {{-- Month --}}
                    <h2 class="text-xl font-bold text-slate-900">
                        {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}
                    </h2>

                    {{-- View Selector --}}
                    <div class="flex rounded-lg border border-slate-200 bg-slate-50 p-1">
                        <button class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-indigo-600 shadow-sm cursor-default">
                            Month
                        </button>
                        <button disabled class="rounded-md px-3 py-1.5 text-sm font-medium text-slate-400 cursor-not-allowed">
                            Week
                        </button>
                        <button disabled class="rounded-md px-3 py-1.5 text-sm font-medium text-slate-400 cursor-not-allowed">
                            Day
                        </button>
                    </div>

                </div>

                {{-- Calendar --}}
                <div class="overflow-x-auto">

                    <div class="min-w-[900px]">

                        {{-- Days of Week --}}
                        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
                            <div class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Sun</div>
                            <div class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Mon</div>
                            <div class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Tue</div>
                            <div class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Wed</div>
                            <div class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Thu</div>
                            <div class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Fri</div>
                            <div class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Sat</div>
                        </div>

                        {{-- Calendar Grid --}}
                        @foreach (collect($this->calendarDays)->chunk(7) as $week)
                            <div class="grid grid-cols-7 border-b border-slate-200 last:border-b-0">
                                @foreach ($week as $cell)
                                    @php
                                        $dateKey = $cell['date']->toDateString();
                                        $dayTasks = $this->tasks[$dateKey] ?? collect();
                                        $isLastCol = ($loop->iteration % 7 === 0);
                                    @endphp

                                    <div
                                        @if ($cell['isCurrentMonth'] && ! $cell['date']->isPast() && auth()->user()->role === 'admin')
                                            wire:click="openCreateModal('{{ $dateKey }}')"
                                        @endif
                                        class="min-h-[140px] p-3 transition cursor-pointer hover:bg-slate-50
                                               {{ $isLastCol ? 'border-b-0' : 'border-r' }} border-slate-200
                                               {{ $cell['isCurrentMonth'] ? '' : 'bg-slate-50' }}
                                               {{ $cell['isToday'] ? 'bg-indigo-50/40' : '' }}
                                               {{ $cell['isToday'] ? 'border-t-2 border-t-indigo-500' : '' }}">
                                        <div class="flex items-center justify-between mb-2">
                                            @if ($cell['isToday'])
                                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">
                                                    {{ $cell['dayNumber'] }}
                                                </span>
                                                <span class="text-[10px] font-semibold uppercase text-indigo-600">Today</span>
                                            @else
                                                <span class="text-sm {{ $cell['isCurrentMonth'] ? 'font-semibold text-slate-700' : 'text-slate-400' }}">
                                                    {{ $cell['dayNumber'] }}
                                                </span>
                                            @endif
                                        </div>

                                        @if ($dayTasks->isNotEmpty())
                                            <div class="space-y-1">
                                                @foreach ($dayTasks->take(3) as $task)
                                                    @php
                                                        $colors = [
                                                            'high' => 'bg-rose-100 text-rose-700',
                                                            'medium' => 'bg-amber-100 text-amber-700',
                                                            'low' => 'bg-indigo-100 text-indigo-700',
                                                        ];
                                                        if ($task->status === 'completed') {
                                                            $colors = [
                                                                'high' => 'bg-emerald-100 text-emerald-700',
                                                                'medium' => 'bg-emerald-100 text-emerald-700',
                                                                'low' => 'bg-emerald-100 text-emerald-700',
                                                            ];
                                                        }
                                                    @endphp
                                                    <button
                                                        wire:click.stop="viewTask({{ $task->task_id }})"
                                                        class="block w-full rounded-md {{ $colors[$task->priority] ?? 'bg-indigo-100 text-indigo-700' }} px-2 py-1.5 text-left text-xs font-medium transition hover:opacity-80"
                                                    >
                                                        {{ $task->title }}
                                                    </button>
                                                @endforeach

                                                @if ($dayTasks->count() > 3)
                                                    <div class="px-2 text-[11px] font-medium text-slate-500">
                                                        +{{ $dayTasks->count() - 3 }} more
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                    </div>

                </div>

                {{-- Calendar Footer / Legend --}}
                <div class="flex flex-wrap items-center gap-5 border-t border-slate-200 px-5 py-4">
                    <span class="text-xs font-semibold text-slate-500">LEGEND</span>

                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                        Low
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        Medium
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                        High
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-600">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        Completed
                    </div>
                </div>

            </div>

        </div>

    </div>

    {{-- CREATE / EDIT MODAL --}}
    @if ($creatingTaskDate || $editingTaskId)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" wire:click="resetForm"></div>

        <div class="relative w-full max-w-lg mx-4 bg-white rounded-xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editingTaskId ? 'Edit Task' : 'New Task' }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $editingTaskId ? 'Update task details.' : 'Create a new task.' }}
                    </p>
                </div>
                <button wire:click="resetForm" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="{{ $editingTaskId ? 'updateTask' : 'storeTask' }}" class="p-6 space-y-5">
@csrf
                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Task Title</label>
                    <input type="text"
                           wire:model="title"
                           placeholder="Enter task title"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                    @error('title') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea wire:model="description"
                              rows="3"
                              placeholder="Task details..."
                              class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition"></textarea>
                    @error('description') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Due Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                        <input type="date"
                               wire:model="due_date"
                               min="{{ now()->toDateString() }}"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                        @error('due_date') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    {{-- Assigned Personnel --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Personnel</label>

                        <input type="text"
                               wire:model.live="assignee_search"
                               placeholder="Search soldiers by name or rank..."
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm mb-2 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">

                        <div class="max-h-52 overflow-y-auto rounded-lg border border-gray-300 divide-y divide-gray-100">
                            @forelse ($this->personnels as $personnel)
                                <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox"
                                           wire:model="assignee_ids"
                                           value="{{ $personnel->personnel_id }}"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <x-avatar :personnel="$personnel" size="xs"/>
                                    <span class="text-sm text-slate-800">
                                        {{ $personnel->last_name }}, {{ $personnel->first_name }}
                                        <span class="text-xs text-slate-400">
                                            &middot; {{ $personnel->rank?->rank_name ?? 'No Rank' }}
                                            @if ($personnel->units) &middot; {{ $personnel->units->unit_name }} @endif
                                        </span>
                                    </span>
                                </label>
                            @empty
                                <div class="px-4 py-4 text-sm text-slate-500">
                                    No soldiers match your search.
                                </div>
                            @endforelse
                        </div>

                        <p class="mt-1 text-xs text-slate-400">
                            Assign one or more soldiers. Mention them with @Name in the title or description to notify them.
                        </p>
                        @error('assignee_ids') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                        @error('personnel_id') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    {{-- Start Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                        <input type="time"
                               wire:model="start_time"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                        @error('start_time') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    {{-- End Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                        <input type="time"
                               wire:model="end_time"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                        @error('end_time') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select wire:model="priority"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        @error('priority') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select wire:model="status"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                        @error('status') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                    {{-- Type --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Task Type</label>
                        <select wire:model="type"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-slate-500 focus:ring-2 focus:ring-slate-200 outline-none transition">
                            <option value="general">General</option>
                            <option value="operations">Operations</option>
                            <option value="training">Training</option>
                            <option value="logistics">Logistics</option>
                            <option value="administrative">Administrative</option>
                        </select>
                        @error('type') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-200">
                    <button type="button"
                            wire:click="resetForm"
                            class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-700 transition">
                        <span wire:loading.remove>{{ $editingTaskId ? 'Update Task' : 'Save Task' }}</span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
    @endif

    {{-- VIEW TASK MODAL --}}
    @if ($viewingTaskId)
        @php
            $viewTask = Task::with(['personnel', 'user', 'assignees.rank', 'assignees.units'])->find($viewingTaskId);
        @endphp
        @if ($viewTask)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" wire:click="resetForm"></div>

            <div class="relative w-full max-w-lg mx-4 bg-white rounded-xl shadow-xl">
                <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $viewTask->title }}</h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Created {{ $viewTask->created_at?->format('M j, Y g:i A') }}
                        </p>
                    </div>
                    <button wire:click="resetForm" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5">

                    @if ($viewTask->description)
                    <div>
                        <p class="text-sm text-gray-700">{{ $viewTask->description }}</p>
                    </div>
                    @endif

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Due Date</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $viewTask->due_date?->format('F j, Y') ?? '—' }}</dd>
                        </div>

                        @if ($viewTask->start_time || $viewTask->end_time)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Time</dt>
                            <dd class="mt-1 font-medium text-gray-900">
                                {{ $viewTask->start_time ? \Carbon\Carbon::parse($viewTask->start_time)->format('g:i A') : '—' }}
                                @if ($viewTask->start_time && $viewTask->end_time) &ndash; @endif
                                {{ $viewTask->end_time ? \Carbon\Carbon::parse($viewTask->end_time)->format('g:i A') : '' }}
                            </dd>
                        </div>
                        @endif

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($viewTask->status === 'completed') bg-emerald-100 text-emerald-700
                                    @elseif ($viewTask->status === 'in_progress') bg-amber-100 text-amber-700
                                    @else bg-slate-100 text-slate-600 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $viewTask->status)) }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Priority</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($viewTask->priority === 'high') bg-rose-100 text-rose-700
                                    @elseif ($viewTask->priority === 'medium') bg-amber-100 text-amber-700
                                    @else bg-indigo-100 text-indigo-700 @endif">
                                    {{ ucfirst($viewTask->priority) }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Type</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ ucfirst($viewTask->type) }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Assigned To</dt>
                            <dd class="mt-2">
                                @if ($viewTask->assignees->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach ($viewTask->assignees as $assignee)
                                            <a href="{{ route('personnel.profile', $assignee->personnel_id) }}"
                                                class="flex items-center gap-3 hover:opacity-80 transition">
                                                <x-avatar :personnel="$assignee" size="xs"/>
                                                <span class="text-sm font-medium text-gray-900">
                                                    {{ $assignee->last_name }}, {{ $assignee->first_name }}
                                                </span>
                                                <span class="text-xs text-slate-500">
                                                    {{ $assignee->rank?->rank_name ?? '—' }}
                                                    @if ($assignee->units) &middot; {{ $assignee->units->unit_name }} @endif
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                @elseif ($viewTask->personnel)
                                    <div class="flex items-center gap-3">
                                        <x-avatar :personnel="$viewTask->personnel" size="xs"/>
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $viewTask->personnel->last_name }}, {{ $viewTask->personnel->first_name }}
                                        </span>
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400">Unassigned</p>
                                @endif
                            </dd>
                        </div>

                    </dl>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                    <button wire:click="resetForm"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Close
                    </button>
                    @if (auth()->user()->role === 'admin')
                    <button wire:click="editTask({{ $viewTask->task_id }})"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                        Edit
                    </button>
                    <button wire:click="confirmDelete({{ $viewTask->task_id }})"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                        Delete
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endif
    @endif

    {{-- DELETE CONFIRMATION MODAL --}}
    @if ($taskToDelete)
        @php
            $deleteTask = Task::find($taskToDelete);
        @endphp
        @if ($deleteTask)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50"></div>

            <div class="relative w-full max-w-md mx-4 bg-white rounded-xl shadow-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-red-100">
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01 M12 5.5a6.5 6.5 0 110 13 6.5 6.5 0 010-13z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Delete <span class="text-blue-600">{{ $deleteTask->title }}</span>?
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">Are you sure you want to delete this task?</p>
                        <p class="mt-1 text-sm text-red-600">This action cannot be undone.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button"
                            wire:click="$set('taskToDelete', null)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="button"
                            wire:click="deleteTask"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @endif
    @endif

</div>
