<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

// Prefix 'events' for all event-related routes
Route::prefix('events')->name('events.')->group(function () {
    // Create a new event (authenticated users only)
    Route::middleware('auth:api')->post('/', [EventController::class, 'createOne']);

    // Get all events
    Route::get('/', [EventController::class, 'readAll']);

    // Get authenticated user's events
    Route::middleware('auth:api')->get('/me', [EventController::class, 'getUserEvents']);

    // Get a specific event by ID
    Route::get('/{id}', [EventController::class, 'readOne']);

    // Update a specific event (authenticated users only)
    Route::middleware('auth:api')->post('/{id}', [EventController::class, 'updateOne']);

    // Delete a specific event (authenticated users only)
    Route::middleware('auth:api')->delete('/{id}', [EventController::class, 'deleteOne']);
});

Route::prefix('bookings')->name('bookings.')->group(function () {
    // Get authenticated user's bookings
    Route::middleware('auth:api')->get('/me', [BookingController::class, 'getUserBookings']);

    // Create a new booking (authenticated users only)
    Route::middleware('auth:api')->post('/', [BookingController::class, 'createOne']);

    // Delete a specific booking (authenticated users only)
    Route::middleware('auth:api')->delete('/{id}', [BookingController::class, 'deleteOne']);
});




