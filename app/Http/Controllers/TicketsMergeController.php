<?php namespace App\Http\Controllers;

use App\Ticket;
use App\Services\Ticketing\TicketRepository;
use Common\Core\BaseController;

class TicketsMergeController extends BaseController {

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * TicketsMergeController constructor.
     *
     * @param TicketRepository $ticketRepository
     */
    public function __construct(TicketRepository $ticketRepository)
	{
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Merged specified tickets.
     *
     * @param int $ticket1
     * @param int $ticket2
     * @return Ticket
     */
    public function merge($ticket1, $ticket2)
    {
	    $this->authorize('update', Ticket::class);

        return $this->ticketRepository->merge($ticket1, $ticket2);
	}
}
