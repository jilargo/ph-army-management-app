<?php

use App\Models\ParentUnit;
use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Task;
use App\Models\Units;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Ranks::create(['rank_name' => 'Private', 'abbreviation' => 'PVT', 'level' => 1]);

    $parent = ParentUnit::create(['parent_name' => '1st Infantry Division']);
    Units::create(['parent_id' => $parent->parent_id, 'unit_name' => 'Alpha Co', 'unit_code' => 'A-CO', 'location' => 'Camp', 'status' => 'Active']);

    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@test.com']);

    $this->james = User::factory()->create(['role' => 'user', 'email' => 'james@test.com']);

    Personnel::create([
        'user_id' => $this->james->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'James',
        'last_name' => 'Largo',
        'status' => 'Active',
    ]);

    $this->boyet = User::factory()->create(['role' => 'user', 'email' => 'boyet@test.com']);

    Personnel::create([
        'user_id' => $this->boyet->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Boyet',
        'last_name' => 'Magneto',
        'status' => 'Active',
    ]);
});

it('notifies every assignee when a task is created', function () {
    $personnelIds = Personnel::pluck('personnel_id')->sort()->values();

    Livewire::actingAs($this->admin)
        ->test('tasks')
        ->set('title', 'Morning drill')
        ->set('due_date', now()->addDay()->toDateString())
        ->set('assignee_ids', $personnelIds->all())
        ->call('storeTask')
        ->assertHasNoErrors();

    $task = Task::first();

    expect($task)->not->toBeNull()
        ->and($task->personnel_id)->toBe((int) $personnelIds->first())
        ->and($task->assignees()->count())->toBe(2)
        ->and($task->assignees()->first()->pivot->assigned_by)->toBe($this->admin->id);

    expect($this->james->unreadNotifications()->count())->toBe(1)
        ->and($this->boyet->unreadNotifications()->count())->toBe(1);

    expect($this->james->unreadNotifications->first()->data['icon'])->toBe('task');
});

it('notifies a soldier mentioned by name in the description', function () {
    $personnel = Personnel::where('last_name', 'Magneto')->first();

    Livewire::actingAs($this->admin)
        ->test('tasks')
        ->set('title', 'Field exercise')
        ->set('description', 'Coordinate with @Magneto for supplies.')
        ->set('due_date', now()->addDay()->toDateString())
        ->call('storeTask')
        ->assertHasNoErrors();

    expect($this->boyet->unreadNotifications()->count())->toBe(1);

    $data = $this->boyet->unreadNotifications->first()->data;

    expect($data['mentioned'])->toBeTrue()
        ->and($data['message'])->toContain('mentions you');
});

it('sends a single notification when a soldier is both assigned and mentioned', function () {
    $personnel = Personnel::where('last_name', 'Largo')->first();

    Livewire::actingAs($this->admin)
        ->test('tasks')
        ->set('title', 'Briefing for @Largo')
        ->set('description', 'Coordinate with @Largo and @Magneto.')
        ->set('due_date', now()->addDay()->toDateString())
        ->set('assignee_ids', [$personnel->personnel_id])
        ->call('storeTask')
        ->assertHasNoErrors();

    expect($this->james->unreadNotifications()->count())->toBe(1)
        ->and($this->boyet->unreadNotifications()->count())->toBe(1);

    expect($this->james->unreadNotifications->first()->data['mentioned'])->toBeFalse();
});

it('notifies only newly added assignees on update', function () {
    $largo = Personnel::where('last_name', 'Largo')->first();
    $magneto = Personnel::where('last_name', 'Magneto')->first();

    $task = Task::create([
        'user_id' => $this->admin->id,
        'personnel_id' => $largo->personnel_id,
        'title' => 'Quarterly audit',
        'due_date' => now()->addDay()->toDateString(),
        'priority' => 'medium',
        'status' => 'pending',
        'type' => 'administrative',
    ]);

    $task->assignees()->attach($largo->personnel_id, ['assigned_by' => $this->admin->id]);

    Livewire::actingAs($this->admin)
        ->test('tasks')
        ->set('editingTaskId', $task->task_id)
        ->set('title', 'Quarterly audit')
        ->set('due_date', now()->addDay()->toDateString())
        ->set('assignee_ids', [$largo->personnel_id, $magneto->personnel_id])
        ->call('updateTask')
        ->assertHasNoErrors();

    expect($this->james->unreadNotifications()->count())->toBe(0)
        ->and($this->boyet->unreadNotifications()->count())->toBe(1);
});

it('rejects creating a task with a past due date', function () {
    Livewire::actingAs($this->admin)
        ->test('tasks')
        ->set('title', 'Backdated task')
        ->set('due_date', now()->subDay()->toDateString())
        ->call('storeTask')
        ->assertHasErrors(['due_date' => 'after_or_equal']);

    expect(Task::count())->toBe(0);
});

it('allows creating a task due today', function () {
    Livewire::actingAs($this->admin)
        ->test('tasks')
        ->set('title', 'Today task')
        ->set('due_date', now()->toDateString())
        ->call('storeTask')
        ->assertHasNoErrors();

    expect(Task::count())->toBe(1);
});

it('rejects updating a task to a past due date', function () {
    $task = Task::create([
        'user_id' => $this->admin->id,
        'title' => 'Forward task',
        'due_date' => now()->addDay()->toDateString(),
        'priority' => 'medium',
        'status' => 'pending',
        'type' => 'general',
    ]);

    Livewire::actingAs($this->admin)
        ->test('tasks')
        ->set('editingTaskId', $task->task_id)
        ->set('title', 'Forward task')
        ->set('due_date', now()->subDay()->toDateString())
        ->call('updateTask')
        ->assertHasErrors(['due_date' => 'after_or_equal']);

    expect($task->fresh()->due_date->isAfter(now()->startOfDay()))->toBeTrue();
});

it('shows assignee details in the task view modal', function () {
    $largo = Personnel::with(['rank', 'units'])->where('last_name', 'Largo')->first();

    $task = Task::create([
        'user_id' => $this->admin->id,
        'personnel_id' => $largo->personnel_id,
        'title' => 'Perimeter patrol',
        'due_date' => now()->addDay()->toDateString(),
        'priority' => 'high',
        'status' => 'pending',
        'type' => 'operations',
    ]);

    $task->assignees()->attach($largo->personnel_id, ['assigned_by' => $this->admin->id]);

    $this->actingAs($this->admin)
        ->get(route('tasks'))
        ->assertOk();

    Livewire::actingAs($this->admin)
        ->test('tasks')
        ->call('viewTask', $task->task_id)
        ->assertSet('viewingTaskId', $task->task_id)
        ->assertSee('Largo, James')
        ->assertSee('Private')
        ->assertSee('Alpha Co');
});
