<?php

namespace App\Notifications\Ticketing\Replies;

use App\Notifications\Ticketing\TicketingNotification;
use App\Reply;
use App\Services\UrlGenerator;
use App\Ticket;
use Auth;
use Str;

abstract class NewReplyCreatedNotif extends TicketingNotification
{
    /**
     * @var Reply
     */
    protected $reply;

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @param Ticket $ticket
     * @param Reply $reply
     */
    public function __construct(Ticket $ticket, Reply $reply)
    {
        $this->reply = $reply;
        $this->ticket = $ticket;
    }

    /**
     * @return array
     */
    protected function mainAction()
    {
        return [
            'label' => 'View Conversation',
            'action' => app(UrlGenerator::class)->ticket($this->ticket),
        ];
    }

    protected function firstLine()
    {
        $action = $this->reply->type === Reply::NOTE_TYPE ? 'added a note' : 'replied';

        if ($this->ticket->assigned_to === Auth::id()) {
            $line = "<strong>:user</strong> $action to your conversation #:ticketId";
        } else if ($this->ticket->assigned_to) {
            $line = "<strong>:user</strong> $action to <strong>:assignee</strong> conversation #:ticketId";
        } else {
            $line = "<strong>:user</strong> $action to unassigned conversation #:ticketId";
        }

         return __($line,
             [
                 'user' => $this->reply->user->display_name,
                 'assignee' => $this->ticket->assigned_to ? $this->ticket->assignee->display_name : null,
                 'ticketId' => $this->ticket->id,
             ]
         );
    }

    protected function secondLine()
    {
        return Str::limit($this->reply->body, 150);
    }

    protected function image()
    {
        return $this->ticket->user->avatar;
    }
}
