<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventUpdatedNotification extends Notification
{
    use Queueable;

    /**
     * @var Event
     */
    protected $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
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
            'message' => 'The event "' . $this->event->name . '" has been updated.',
            'type' => 'event_update',
        ];
    }

    public function toBroadcast(object $notifiable)
    {
        return new DatabaseMessage([
            'event_id' => $this->event->id,
            'message' => 'The event "' . $this->event->name . '" has been updated.',
            'type' => 'event_update',
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            // Add additional fields if needed
        ];
    }
}
