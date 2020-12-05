<?php namespace App\Http\Controllers;

use App\Reply;
use App\Ticket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ModifyReplies;
use App\Services\Ticketing\ReplyRepository;
use App\Services\Ticketing\TicketRepository;
use App\Services\Ticketing\TicketReplyCreator;
use Common\Core\BaseController;

class TicketRepliesController extends BaseController {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ReplyRepository
     */
    private $replyRepository;

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @param Request $request
     * @param ReplyRepository $replyRepository
     * @param TicketRepository $ticketRepository
     */
    public function __construct(Request $request, ReplyRepository $replyRepository, TicketRepository $ticketRepository)
    {
        $this->request = $request;
        $this->replyRepository = $replyRepository;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Paginate specified ticket replies.
     *
     * @param int $ticketId
     * @return JsonResponse
     */
    public function index($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $this->authorize('index', [Reply::class, $ticket]);

        $params = $this->request->all();
        $params['ticket_id'] = $ticketId;

        $pagination = $this->replyRepository->paginate($params);

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * Create a new reply for specified ticket.
     *
     * @param integer $ticketId
     * @param string $type
     * @param ModifyReplies $request
     * @param TicketReplyCreator $replyCreator
     *
     * @return JsonResponse
     */
    public function store($ticketId, $type, ModifyReplies $request, TicketReplyCreator $replyCreator)
    {
        $ticket = $this->ticketRepository->find($ticketId);

        $this->authorize('store', [Reply::class, $ticket]);

        $reply = $replyCreator->create($ticket, $request->all(), $type, $request->user());

        return $this->success(['data' => $reply->toArray()], 201);
    }
}
