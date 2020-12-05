<?php

namespace App\Notifications;

use App\Services\UrlGenerator;
use Common\Core\Prerender\Actions\ReplacePlaceholders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TriggerEmailAction extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var array
     */
    public $data;

    /**
     * @var string
     */
    public $message;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->message = app(ReplacePlaceholders::class)->execute($data['message'], $data);
    }

    /**
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->data['subject'])
            ->line($this->message)
            ->action(__('View Ticket'), app(UrlGenerator::class)->ticket($this->data['ticket']));
    }
}
