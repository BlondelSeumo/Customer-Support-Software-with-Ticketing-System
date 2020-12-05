<?php namespace App\Services\Triggers\Actions;

use App\Action;
use App\Ticket;
use App\Services\Ticketing\TicketRepository;

class DeleteTicketAction implements TriggerActionInterface {

    /**
     * TicketRepository service instance.
     *
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * DeleteTicketAction constructor.
     *
     * @param TicketRepository $ticketRepository
     */
    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Perform specified action on ticket.
     *
     * @param Ticket $ticket
     * @param Action $action
     * @param \App\Trigger $trigger
     * @return Ticket
     */
    public function perform(Ticket $ticket, Action $action, \App\Trigger $trigger)
    {
        $this->ticketRepository->deleteTickets([$ticket->id]);

        return $ticket;
    }
}
