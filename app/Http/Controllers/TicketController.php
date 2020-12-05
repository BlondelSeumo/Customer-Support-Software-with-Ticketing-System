<?php namespace App\Http\Controllers;

use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use App\Services\Ticketing\Actions\PaginateTickets;
use App\Services\Ticketing\TicketRepository;
use App\Ticket;
use Auth;
use Common\Core\BaseController;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @param Request $request
     * @param TicketRepository $ticketRepository
     */
    public function __construct(Request $request, TicketRepository $ticketRepository)
    {
        $this->request = $request;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', [Ticket::class, $this->request->get('userId')]);

        $this->validate($this->request, [
            'tags'        => 'string|min:1',
            'assigned_to' => 'integer',
        ]);

        $pagination = app(PaginateTickets::class)->execute($this->request->all());

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * Return specified ticket.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $ticket = $this->ticketRepository->findOrFail($id);

        $this->authorize('show', $ticket);

        $ticket = $this->ticketRepository->loadConversation($ticket);

        return $this->success(['ticket' => $ticket]);
    }

    /**
     * @return mixed
     */
    public function store()
    {
        $this->authorize('store', Ticket::class);

        $this->validate($this->request, [
            'user_id'       => 'integer|exists:users,id',
            'subject'       => 'required|min:3|max:255',
            'category'      => 'required|integer|min:1|envatoSupportActive',
            'body'          => 'required|min:3',
            'uploads'       => 'array|max:10|exists:file_entries,id',
            'tags'          => 'array|min:1|max:10',
            'tags.*'        => 'integer|min:1',
        ]);

        $ticket = $this->ticketRepository->create($this->request->all());

        event(new TicketCreated($ticket));

        return response($ticket, 201);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function update($id)
    {
        $ticket = $this->ticketRepository->findOrFail($id);
        $this->authorize('update', $ticket);

        $this->validate($this->request, [
            'subject'       => 'min:3|max:255',
            'category'      => 'integer|min:1',
            'tags'          => 'array|min:1|max:10',
            'tags.*'        => 'integer|min:1',
        ]);

        $ticket = $this->ticketRepository->update($ticket, $this->request->all());

        event(new TicketUpdated($ticket));

        return $this->success(['ticket' => $ticket]);
    }

    public function destroy()
    {
        $this->authorize('destroy', Ticket::class);

        $this->validate($this->request, [
            'ids'    => 'required|array',
            'ids.*'  => 'required|integer',
        ]);

        $this->ticketRepository->deleteTickets($this->request->get('ids'));

        return $this->success([], 204);
    }

    public function nextActiveTicket($tagId): JsonResponse
    {
        $this->authorize('index', Ticket::class);

        $query = app(Ticket::class)
            ->join('taggables', 'taggables.taggable_id', '=', 'tickets.id')
            ->where('taggables.taggable_type', Ticket::class)
            ->join('tags', function(JoinClause $join) {
                $join->on('tags.id', '=', 'taggables.tag_id');
                $join->on('tags.type', '=', DB::raw("'status'"));
            })->select('tickets.*', 'tags.name as status')
            ->where(function(Builder $builder) {
                $builder->whereNull('assigned_to')->orWhere('assigned_to', Auth::id());
            });

        if ($tagId !== 'closed') {
            $query->whereNull('closed_at');
        }

        app(PaginateTickets::class)->filterByTag($tagId, $query);

        $ticket = $query->orderByStatus()->first();

        return $this->success(['ticket' => $ticket]);
    }
}
