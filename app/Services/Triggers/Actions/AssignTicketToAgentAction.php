<?php namespace App\Services\Triggers\Actions;

use App\Action;
use App\Ticket;
use App\Services\Ticketing\TicketRepository;

class AssignTicketToAgentAction implements TriggerActionInterface {

    /**
     * TicketRepository service instance.
     *
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * AssignToAgentAction constructor.
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
        $agentId = json_decode($action->pivot['action_value'])->agent_id;

        $this->ticketRepository->assignToAgent([$ticket->id], $agentId);
        $ticket->assigned_to = $agentId;

        return $ticket;
    }
}
