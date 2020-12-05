<?php

namespace App\Listeners;

use App\Events\TicketReplyCreated;
use App\Notifications\Ticketing\Replies\Agent\AgentRepliedToMyTicketNotif;
use App\Notifications\Ticketing\Replies\Agent\AgentRepliedToSomeoneElseTicketNotif;
use App\Notifications\Ticketing\Replies\Agent\AgentRepliedToUnassignedTicketNotif;
use App\Notifications\Ticketing\Replies\Customer\CustomerRepliedToMyTicketNotif;
use App\Notifications\Ticketing\Replies\Customer\CustomerRepliedToSomeoneElseTicketNotif;
use App\Notifications\Ticketing\Replies\Customer\CustomerRepliedToUnassignedTicketNotif;
use App\Reply;
use App\Ticket;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Notification;

class SendReplyCreatedNotif implements ShouldQueue
{
    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @var Reply
     */
    private $reply;

    /**
     * @param Ticket $ticket
     * @param Reply $reply
     */
    public function __construct(Ticket $ticket, Reply $reply)
    {
        $this->ticket = $ticket;
        $this->reply = $reply;
    }

    /**
     * @param  TicketReplyCreated  $event
     * @return void
     */
    public function handle(TicketReplyCreated $event)
    {
        $ticket = $this->ticket->find($event->ticketId);
        $reply = $this->reply->find($event->replyId);
        $replyFromAgent = $reply->user->isAgent();

        if ($ticket->assigned_to) {
            // ticket assigned to me
            $notif1 = $replyFromAgent ? AgentRepliedToMyTicketNotif::class : CustomerRepliedToMyTicketNotif::class;
            $user = app(User::class)
                ->where('id', $ticket->assigned_to)
                ->where('id', '!=', $reply->user_id)
                ->whereNeedsNotificationFor($notif1::NOTIF_ID)
                ->first();
            if ($user) {
                Notification::send($user, new $notif1($ticket, $reply));
            }

            // ticket assigned to someone else
            $notif2 = $replyFromAgent ? AgentRepliedToSomeoneElseTicketNotif::class : CustomerRepliedToSomeoneElseTicketNotif::class;
            $users = app(User::class)
                ->where('id', '!=', $ticket->assigned_to)
                ->where('id', '!=', $reply->user_id)
                ->whereNeedsNotificationFor($notif2::NOTIF_ID)
                ->get();
            if ($users->isNotEmpty()) {
                Notification::send($users, new $notif2($ticket, $reply));
            }
        } else {
            // ticket is unassigned
            $notif3 = $replyFromAgent ? AgentRepliedToUnassignedTicketNotif::class : CustomerRepliedToUnassignedTicketNotif::class;
            $users = app(User::class)
                ->where('id', '!=', $reply->user_id)
                ->whereNeedsNotificationFor($notif3::NOTIF_ID)
                ->get();
            if ($users->isNotEmpty()) {
                Notification::send($users, new $notif3($ticket, $reply));
            }
        }
    }
}
