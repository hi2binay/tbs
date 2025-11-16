<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::resource('events', EventController::class)->only(['index', 'show']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('events/{event}/bookings')->name('bookings.')->group(function () {
        Route::get('/create', [BookingController::class, 'create'])->name('create');
        Route::post('/', [BookingController::class, 'store'])->name('store');
    });

    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
});

Route::middleware(['auth', 'admin'])->prefix('backend')->name('backend.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Backend\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('events', \App\Http\Controllers\Backend\EventController::class);

    Route::prefix('events/{event}/ticket-types')->name('ticket-types.')->group(function () {
        Route::get('/create', [\App\Http\Controllers\Backend\TicketTypeController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Backend\TicketTypeController::class, 'store'])->name('store');
        Route::get('/{ticketType}/edit', [\App\Http\Controllers\Backend\TicketTypeController::class, 'edit'])->name('edit');
        Route::put('/{ticketType}', [\App\Http\Controllers\Backend\TicketTypeController::class, 'update'])->name('update');
        Route::delete('/{ticketType}', [\App\Http\Controllers\Backend\TicketTypeController::class, 'destroy'])->name('destroy');
    });

    Route::resource('users', \App\Http\Controllers\Backend\UserController::class)->except(['create', 'store']);

    Route::get('bookings', [\App\Http\Controllers\Backend\BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}', [\App\Http\Controllers\Backend\BookingController::class, 'show'])->name('bookings.show');
    Route::post('bookings/{booking}/refund', [\App\Http\Controllers\Backend\BookingController::class, 'refund'])->name('bookings.refund');
});

require __DIR__.'/auth.php';
