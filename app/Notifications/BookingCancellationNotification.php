<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancellationNotification extends Notification
{
    use Queueable;

    protected $booking;
    protected $event;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->event = $booking->event;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable)
    {
        return [
            'event_id' => $this->event->id,
            'message' => 'A booking has been canceled for your event: ' . $this->event->name,
            'type' => 'cancellation',
        ];
    }

    public function toBroadcast(object $notifiable)
    {
        return new DatabaseMessage([
            'event_id' => $this->event->id,
            'message' => 'A booking has been canceled for your event: ' . $this->event->name,
            'type' => 'cancellation',
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'message' => 'A booking has been canceled for your event: ' . $this->event->name,
            'type' => 'cancellation',
        ];
    }
}

