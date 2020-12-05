<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use App\Notifications\Ticketing\TicketCreatedNotif;
use App\User;
use Notification;

class SendTicketCreatedNotif
{
    /**
     * @param  TicketCreated  $event
     * @return void
     */
    public function handle(TicketCreated $event)
    {
        $users = app(User::class)->whereNeedsNotificationFor(TicketCreatedNotif::NOTIF_ID)->get();
        Notification::send($users, new TicketCreatedNotif($event->ticket));
    }
}
