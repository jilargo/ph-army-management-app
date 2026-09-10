<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'admin-dashboard')
    ->middleware(['auth', 'admin']);
Route::livewire('/personnel/create', 'personnel.create')
    ->middleware(['auth', 'admin'])
    ->name('personnel.create');
Route::livewire('/personnel/index', 'personnel.index')
    ->middleware(['auth', 'admin'])
    ->name('personnel.index');
Route::livewire('/units', 'units.index')
    ->middleware(['auth', 'admin'])
    ->name('units.index');
Route::livewire('/personnel/{personnel}/edit', 'personnel.edit')
    ->middleware(['auth', 'admin'])
    ->name('personnel.edit');
Route::livewire('/personnel/{personnel}', 'personnel.profile')
    ->middleware(['auth'])
    ->name('personnel.profile');
Route::livewire('/register', 'auth.register')
    // ->middleware(['auth', 'admin'])
    ->name('register');
Route::livewire('/login', 'auth.login')
    ->name('login');
Route::livewire('/user-dashboard', 'personnel.user-dashboard')
    ->middleware(['auth'])
    ->name('user-dashboard');
Route::livewire('/tasks', 'tasks')
    ->middleware(['auth'])
    ->name('tasks');
Route::livewire('/leaves', 'leaves.index')
    ->middleware(['auth', 'admin'])
    ->name('leaves.index');
Route::livewire('/leaves/create', 'leaves.create')
    ->middleware(['auth'])
    ->name('leaves.create');
Route::livewire('/promotions', 'promotions.index')
    ->middleware(['auth'])
    ->name('promotions.index');
Route::livewire('/apply', 'applicant.apply')
    ->name('apply');
Route::livewire('/enlistments', 'enlistments.index')
    ->middleware(['auth', 'admin'])
    ->name('enlistments.index');
Route::livewire('/reports', 'reports.index')
    ->middleware(['auth', 'admin'])
    ->name('reports.index');
Route::get('/reports/personnel', [ReportController::class, 'personnel'])
    ->middleware(['auth', 'admin'])
    ->name('reports.personnel');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');
