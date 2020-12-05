<?php

namespace App\Notifications\Ticketing;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

abstract class TicketingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array
     */
    abstract protected function mainAction();

    /**
     * @return string
     */
    abstract protected function firstLine();

    /**
     * @return string
     */
    abstract protected function secondLine();

    /**
     * @return string
     */
    abstract protected function image();

    /**
     * @param  User  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = [];

        if ($sub = $notifiable->notificationSubscriptions->where('notif_id', static::NOTIF_ID)->first()) {
            foreach (array_filter($sub->channels) as $channel => $isSelected) {
                if ($channel === 'browser') {
                    $channels = array_merge($channels, ['database', 'broadcast']);
                } else if ($channel === 'email') {
                    $channels[] = 'mail';
                } else {
                    $channels[] = $channel;
                }
            }
        }

        return $channels;
    }

    /**
     * @param  User  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $mainAction = $this->mainAction();
        $subject = method_exists($this, 'subject') ? $this->subject() : strip_tags($this->firstLine());
        return (new MailMessage)
            ->subject($subject)
            ->line($this->firstLine())
            ->line($this->secondLine())
            ->action($mainAction['label'], $mainAction['action']);
    }

    /**
     * @param User $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->image($this->image())
            ->from('the cab')
            ->content(strip_tags($this->firstLine()))
            ->error()
            ->attachment(function (SlackAttachment $attachment) {
                $mainAction = $this->mainAction();
                $attachment->title($mainAction['label'], $mainAction['action'])
                    ->content(strip_tags($this->secondLine()));
            });
    }

    /**
     * @param User $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'image' => $this->image(),
            'mainAction' => $this->mainAction(),
            'lines' => [
                ['content' => $this->firstLine()],
                ['content' => $this->secondLine()],
            ],
        ];
    }
}
