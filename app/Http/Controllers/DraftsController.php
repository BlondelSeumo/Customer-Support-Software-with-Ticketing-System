<?php namespace App\Http\Controllers;

use App\Services\Ticketing\ReplyRepository;
use App\Services\Ticketing\TicketRepository;
use App\Events\TicketUpdated;
use Common\Core\BaseController;

class DraftsController extends BaseController
{
    /**
     * @var ReplyRepository
     */
    private $replyRepository;

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @param ReplyRepository $replyRepository
     * @param TicketRepository $ticketRepository
     */
    public function __construct(ReplyRepository $replyRepository, TicketRepository $ticketRepository)
    {
        $this->replyRepository = $replyRepository;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Delete specified draft.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $draft = $this->replyRepository->findOrFail($id);

        $this->authorize('destroy', [$draft, 'drafts']);

        $ticket = $this->ticketRepository->find($draft->ticket_id);

        $this->replyRepository->delete($draft);

        event(new TicketUpdated($ticket, $ticket));

        return $this->success(null, 204);
    }
}
