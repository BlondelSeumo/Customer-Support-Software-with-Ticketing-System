<?php

namespace App\Listeners;

use App\Events\TicketsAssigned;
use App\Notifications\Ticketing\Assigned\TicketAssignedNotif;
use App\Notifications\Ticketing\Assigned\TicketAssignedNotMeNotif;
use App\User;
use Auth;
use Notification;

class SendTicketsAssignedNotif
{
    /**
     * @param  TicketsAssigned  $event
     * @return void
     */
    public function handle(TicketsAssigned $event)
    {
        $ticket = $event->updatedTickets->first();
        $assigner = Auth::user();

        // not need to notify user if they assigned ticket to themselves
        if ($assigner->id !== $ticket['assigned_to']) {
            // ticket assigned to me
            $user = app(User::class)
                ->where('id', $ticket['assigned_to'])
                ->whereNeedsNotificationFor(TicketAssignedNotif::NOTIF_ID)
                ->first();
            if ($user) {
                Notification::send($user, new TicketAssignedNotif($event->updatedTickets, $assigner));
            }
        }

        // ticket assigned to someone else
        $users = app(User::class)
            ->where('id', '!=', $ticket['assigned_to'])
            ->whereNeedsNotificationFor(TicketAssignedNotMeNotif::NOTIF_ID)
            ->get();
        if ($users->isNotEmpty()) {
            Notification::send($users, new TicketAssignedNotMeNotif($event->updatedTickets, $assigner, $ticket->assignee));
        }
    }
}
