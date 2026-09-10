<?php

use App\Models\Leaves;
use App\Models\LeaveType;
use App\Models\ParentUnit;
use App\Models\Personnel;
use App\Models\Ranks;
use App\Models\Units;
use App\Models\User;
use App\Notifications\LeaveStatusNotification;
use App\Notifications\LeaveSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Ranks::create(['rank_name' => 'Private', 'abbreviation' => 'PVT', 'level' => 1]);

    $parent = ParentUnit::create(['parent_name' => '1st Infantry Division']);
    Units::create(['parent_id' => $parent->parent_id, 'unit_name' => 'Alpha Co', 'unit_code' => 'A-CO', 'location' => 'Camp', 'status' => 'Active']);

    LeaveType::create(['leave_name' => 'Vacation Leave', 'description' => 'Rest and recreation.']);

    $this->admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@test.com']);
});

it('notifies all admins when a soldier files a leave', function () {
    Notification::fake();

    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);
    $ownPersonnel = Personnel::create([
        'user_id' => $soldier->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
    ]);

    Livewire::actingAs($soldier)
        ->test('leaves.create')
        ->set('personnel_id', $ownPersonnel->personnel_id)
        ->set('leave_type_id', 1)
        ->set('start_date', now()->addDay()->toDateString())
        ->set('end_date', now()->addDays(2)->toDateString())
        ->set('reason', 'Personal reasons')
        ->call('store_leave')
        ->assertRedirect(route('user-dashboard'));

    Notification::assertSentTo($this->admin, LeaveSubmittedNotification::class);
});

it('stores an unread notification for admins when a leave is filed', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);
    $ownPersonnel = Personnel::create([
        'user_id' => $soldier->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
    ]);

    Livewire::actingAs($soldier)
        ->test('leaves.create')
        ->set('personnel_id', $ownPersonnel->personnel_id)
        ->set('leave_type_id', 1)
        ->set('start_date', now()->addDay()->toDateString())
        ->set('end_date', now()->addDays(2)->toDateString())
        ->set('reason', 'Personal reasons')
        ->call('store_leave');

    expect($this->admin->unreadNotifications()->count())->toBe(1);

    $data = $this->admin->notifications->first()->data;

    expect($data['title'])->toBe('New Leave Request')
        ->and($data['message'])->toContain('Dela Cruz')
        ->and($data['url'])->toContain('/leaves');
});

it('contains the rejection reason when a leave is rejected', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);
    $ownPersonnel = Personnel::create([
        'user_id' => $soldier->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
    ]);

    $leave = Leaves::create([
        'personnel_id' => $ownPersonnel->personnel_id,
        'user_id' => $soldier->id,
        'leave_type_id' => 1,
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'reason' => 'Personal reasons',
        'status' => 'pending',
    ]);

    Livewire::actingAs($this->admin)
        ->test('leaves.index')
        ->set('viewingLeaveId', $leave->leave_id)
        ->set('remarks', 'Conflict with training schedule')
        ->call('reject');

    $soldier->refresh();

    expect($soldier->unreadNotifications()->count())->toBe(1);

    $data = $soldier->notifications->first()->data;

    expect($data['status'])->toBe('rejected')
        ->and($data['remarks'])->toBe('Conflict with training schedule');
});

it('records the leave as approved for the soldier notification', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);
    $ownPersonnel = Personnel::create([
        'user_id' => $soldier->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
    ]);

    $leave = Leaves::create([
        'personnel_id' => $ownPersonnel->personnel_id,
        'user_id' => $soldier->id,
        'leave_type_id' => 1,
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'reason' => 'Personal reasons',
        'status' => 'pending',
    ]);

    Livewire::actingAs($this->admin)
        ->test('leaves.index')
        ->set('viewingLeaveId', $leave->leave_id)
        ->call('approve');

    $soldier->refresh();

    expect($soldier->unreadNotifications()->count())->toBe(1);

    $data = $soldier->notifications->first()->data;

    expect($data['status'])->toBe('approved');
});

it('marks a notification as read when opened', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);
    $personnel = Personnel::create([
        'user_id' => $soldier->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
    ]);

    $soldier->notify(new LeaveStatusNotification(
        Leaves::create([
            'personnel_id' => $personnel->personnel_id,
            'user_id' => $soldier->id,
            'leave_type_id' => 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Personal reasons',
            'status' => 'approved',
        ])
    ));

    $notification = $soldier->fresh()->notifications->first();

    Livewire::actingAs($soldier)
        ->test('notification-bell')
        ->call('openNotification', $notification->id);

    expect($soldier->fresh()->unreadNotifications()->count())->toBe(0);
});

it('marks all notifications as read', function () {
    $soldier = User::factory()->create(['role' => 'user', 'email' => 'soldier@test.com']);
    $personnel = Personnel::create([
        'user_id' => $soldier->id,
        'rank_id' => 1,
        'unit_id' => 1,
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'status' => 'Active',
    ]);

    for ($i = 0; $i < 3; $i++) {
        $soldier->notify(new LeaveStatusNotification(
            Leaves::create([
                'personnel_id' => $personnel->personnel_id,
                'user_id' => $soldier->id,
                'leave_type_id' => 1,
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'reason' => 'Personal reasons',
                'status' => 'approved',
            ])
        ));
    }

    Livewire::actingAs($soldier)
        ->test('notification-bell')
        ->call('markAllAsRead');

    expect($soldier->fresh()->unreadNotifications()->count())->toBe(0);
});
