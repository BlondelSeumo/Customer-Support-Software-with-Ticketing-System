<?php

namespace App\Notifications;

use App\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Ticket
     */
    public $ticket;

    /**
     * @param Ticket $ticket
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
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
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Request Received'))
            ->line(__(
                "Thanks for getting in touch! This is an automatic email just to let you know that we've received your request and your ticket reference is :ticketId.",
                ['ticketId' => $this->ticket->id]
            ))
            ->line(__('One of our support agents will get back to you shortly. Please do not submit multiple tickets for the same request, as this will not result in a faster response time.'))
            ->action('View Help Center', url('/help-center'));
    }
}
