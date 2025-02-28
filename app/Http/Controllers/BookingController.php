<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmationMail;
use App\Mail\ConfirmationMail;
use App\Models\Booking;
use App\Models\Event;
use App\Notifications\BookingCancellationNotification;
use App\Notifications\BookingConfirmation;
use App\Notifications\BookingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 * Booking Controller
 *
 * Handles all booking-related operations including creation, cancellation,
 * and retrieval of user bookings
 *
 * @category Controllers
 * @package  App\Http\Controllers
 * @author   Your Name <your.email@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://your-project-documentation-url.com
 */
class BookingController extends CrudController
{
    protected $table = 'bookings';
    protected $modelClass = Booking::class;
    protected $restricted = ['delete'];

    /**
     * Get the database table name
     *
     * @return string
     */
    protected function getTable()
    {
        return $this->table;
    }

    /**
     * Get the model class name
     *
     * @return string
     */
    protected function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * Handle post-creation tasks for a booking
     *
     * @param mixed   $modelClass The model class
     * @param Request $request    The HTTP request
     * @return void
     */
    protected function afterCreateOne($modelClass, Request $request)
    {
        $user = Auth::user();
        // Get the latest created booking
        $booking = $modelClass::latest()->first();
        if ($booking) {
            $event = $booking->event; // Get the associated event
            $organizer = $event->organizer; // Get the event organizer
    
            $eventPermissions = [
                'bookings.' . $booking->id . '.delete',
            ];
    
            // Grant permissions to the organizer
            if ($organizer) {
                foreach ($eventPermissions as $permissionName) {
                    $organizer->givePermission($permissionName);
                }
                $organizer->notify(new BookingNotification($booking));
                $user->notify(new BookingConfirmation($booking));
            }
        }
        
    }

    protected function afterDeleteOne($modelClass, Request $request)
{
    // Get the latest deleted booking
    $booking = $modelClass::latest()->first();

    if ($booking) {
        $event = $booking->event; // Get the associated event
        $user = $booking->user; // Get the user who made the booking
        $organizer = $event->organizer; // Get the event organizer

        // Check if the user who canceled the booking is the organizer
        if ($user->id === $organizer->id) {
            // If the organizer canceled the booking, notify the user who made the booking
            $userToNotify = $booking->user; // The user who made the booking
            $userToNotify->notify(new BookingCancellationNotification($booking));
        } else {
            // If a user canceled their booking, notify the organizer
            $organizer->notify(new BookingCancellationNotification($booking));
        }
    }
}

    
    /**
     * Create a new booking
     *
     * @param Request $request The HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOne(Request $request)
    {
        $request->merge(['user_id' => Auth::id()]);
        $event = Event::findOrFail($request->event_id);
        
        if ($event->organizer_id == Auth::id()) {
            return response()->json(['error' => 'Event organizer cannot book their own event.'], 403);
        }

        if ($request->spots > $event->available_spots) {
            return response()->json(
                ['error' => "Only {$event->available_spots} spots remaining."], 
                400
            );
        }
        return parent::createOne($request);
    }

    /**
     * Cancel a booking by the event organizer
     *
     * @param Request $request The HTTP request
     * @param Event   $event   The event model
     * @param Booking $booking The booking to cancel
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelByOrganizer(Request $request, Event $event, Booking $booking)
    {
        // Check if the user is the event organizer
        if ($event->organizer_id !== auth()->id()) {
            return response()->json(
                ['message' => 'You are not the organizer of this event.'], 
                403
            );
        }
    
        // Delete the booking
        $booking->delete();
    
        return response()->json(['message' => 'Booking cancelled successfully.']);
    }

    /**
     * Get all bookings for the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserBookings()
    {
        $user = auth()->user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Get user's bookings with event details
        $bookings = $user->bookings()->with('event')->get();

        return response()->json($bookings);
    }
}
