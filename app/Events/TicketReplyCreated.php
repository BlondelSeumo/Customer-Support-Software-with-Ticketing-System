<?php namespace App\Events;

use App\Reply;
use App\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class TicketReplyCreated implements ShouldQueue, ShouldBroadcast
{
    use SerializesModels, InteractsWithSockets;

    /**
     * @var int
     */
    public $creatorId;

    /**
     * @var string
     */
    public $replyType;

    /**
     * @var int
     */
    public $replyId;

    /**
     * @var int
     */
    public $ticketId;

    /**
     * @param Ticket $ticket
     * @param Reply $reply
     * @internal param Reply $reply
     */
    public function __construct(Ticket $ticket, Reply $reply)
    {
        $this->dontBroadcastToCurrentUser();

        $this->creatorId = $ticket->user_id;
        $this->replyId = $reply->id;
        $this->replyType = $reply->type;
        $this->ticketId = $reply->ticket_id;
    }

    /**
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('tickets');
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return $this->replyType !== Reply::DRAFT_TYPE;
    }
}
