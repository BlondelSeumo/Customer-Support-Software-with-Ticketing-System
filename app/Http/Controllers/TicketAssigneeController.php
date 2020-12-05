<?php namespace App\Http\Controllers;

use App\Events\TicketsAssigned;
use App\Ticket;
use Illuminate\Http\Request;
use App\Events\TicketUpdated;
use App\Services\Ticketing\TicketRepository;
use Common\Core\BaseController;

class TicketAssigneeController extends BaseController
{
    /**
     * @var TicketRepository
     */
    private $tickets;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param TicketRepository $tickets
     * @param Request $request
     */
    public function __construct(TicketRepository $tickets, Request $request)
    {
        $this->tickets = $tickets;
        $this->request   = $request;
    }

    /**
     * Assign ticket(s) to specified agent.
     */
    public function change()
    {
        $this->authorize('update', Ticket::class);

        $this->validate($this->request, [
            'tickets'   => 'required|array|min:1',
            'tickets.*' => 'required|integer',
        ]);

        $ticketIds = $this->request->get('tickets');
        $assignee = $this->request->get('user_id');
        $originalTickets = $this->tickets->find($ticketIds);

        // get only IDs of tickets that are not assigned to this user already
        $ticketIds = array_filter($ticketIds, function($ticketId) use($originalTickets, $assignee) {
            return $originalTickets->find($ticketId)->assigned_to !== $assignee;
        });

        if ($ticketIds) {
            $this->tickets->assignToAgent($ticketIds, $assignee);
            $updatedTickets = $this->tickets->find($ticketIds);

            // fire ticket updated event for each updated ticket
            foreach($originalTickets as $k => $ticket) {
                event(new TicketUpdated($updatedTickets[$k], $originalTickets[$k]));
            }

            event(new TicketsAssigned($originalTickets, $updatedTickets));
        }

        return $this->success();
    }
}
