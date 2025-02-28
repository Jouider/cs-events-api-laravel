<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $booking;
    protected $event;
    protected $user;
    protected $spots;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->event = $booking->event;
        $this->user = $booking->user;
        $this->spots = $booking->spots;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase(object $notifiable)
    {
        return [
            'event_id' => $this->event->id,
            'message' => $this->user->name . ' a réservé ' . $this->spots . ' spot(s) dans votre événement ' . $this->event->name,
            'type' => 'booking',
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable)
    {
        return new DatabaseMessage([
            'event_id' => $this->event->id,
            'message' => $this->user->name . ' a réservé ' . $this->spots . ' spot(s) dans votre événement ' . $this->event->name,
            'type' => 'booking',
        ]);
    }

    /**
     * Get the broadcast channel(s) the event should be sent to.
     */
    public function broadcastOn(): array
    {
        return ['private.user.'.$this->event->organizer_id];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs()
    {
        // Définit un nom d'événement personnalisé, par exemple "booking.created"
        return 'booking.created';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
