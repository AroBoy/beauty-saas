<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\WorkerController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'tenant', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::resource('workers', WorkerController::class)->except('show');
    Route::get('clients/search', [ClientController::class, 'search'])->name('clients.search');
    Route::resource('clients', ClientController::class)->except('show');
    Route::get('clients/{client}', [ClientController::class, 'show'])
        ->whereNumber('client')
        ->name('clients.show');
    Route::resource('services', ServiceController::class)->except('show');
    Route::resource('appointments', AppointmentController::class)->except('show');
    Route::get('appointments/resources', [AppointmentController::class, 'resources'])->name('appointments.resources');
    Route::get('appointments/feed', [AppointmentController::class, 'events'])->name('appointments.feed');
    Route::patch('appointments/{appointment}/move', [AppointmentController::class, 'move'])->name('appointments.move');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
