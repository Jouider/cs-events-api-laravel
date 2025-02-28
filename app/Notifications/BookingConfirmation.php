<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmation extends Notification
{
    use Queueable;

    public $booking;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Confirmation de votre réservation')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre réservation pour l\'événement "' . $this->booking->event->name . '" a été confirmée.')
            ->line('Détails de la réservation :')
            ->line('Date de l\'événement : ' . $this->booking->event->date)
            ->line('Nombre de places réservées : ' . $this->booking->spots)
            ->action('Voir l\'événement', url('/events/' . $this->booking->event->id))
            ->line('Merci de votre réservation !')
            ->line('Si vous avez des questions, contactez-nous à l\'adresse suivante : support@tonsite.com.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            // données de la notification
        ];
    }
}