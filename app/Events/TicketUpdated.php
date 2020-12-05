<?php namespace App\Events;

use App\Ticket;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TicketUpdated implements ShouldQueue
{
    use SerializesModels;

    /**
     * Updated ticket model instance.
     *
     * @var Ticket
     */
    public $updatedTicket;

    /**
     * Original (before it was updated) ticket model instance.
     *
     * @var array
     */
    public $originalTicket;

    /**
     * @param Ticket $updatedTicket
     * @param Ticket $originalTicket
     */
    public function __construct(Ticket $updatedTicket, Ticket $originalTicket = null)
    {
        $this->updatedTicket = $updatedTicket;

        //convert Ticket model to array, otherwise there might be
        //issues with original ticket data being updated by eloquent
        $originalTicket = $originalTicket ? $originalTicket : $updatedTicket;

        // TODO: move "status" to main ticket model instead of tags
        $status = $originalTicket->status;
        $this->originalTicket = $originalTicket->toArray();
        $this->originalTicket['status'] = $status;
    }
}
