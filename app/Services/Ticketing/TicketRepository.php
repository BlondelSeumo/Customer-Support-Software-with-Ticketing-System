<?php namespace App\Services\Ticketing;

use Carbon\Carbon;
use DB;
use Auth;
use App\Tag;
use App\Reply;
use App\Ticket;
use Common\Settings\Settings;
use Illuminate\Database\Query\JoinClause;
use Arr;
use App\Services\TagRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Str;

class TicketRepository {

    /**
     * Reply model instance.
     *
     * @var Reply
     */
    private $reply;

    /**
     * Ticket model instance.
     *
     * @var Ticket
     */
    private $ticket;

    /**
     * Tag model instance.
     *
     * @var Tag
     */
    private $tag;

    /**
     * TagRepository instance.
     *
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * ReplyRepository instance.
     *
     * @var ReplyRepository
     */
    private $replyRepository;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * TicketRepository constructor.
     *
     * @param Tag $tag
     * @param Reply $reply
     * @param Ticket $ticket
     * @param Settings $settings
     * @param TagRepository $tagRepository
     * @param ReplyRepository $replyRepository
     */
    public function __construct(
        Tag $tag,
        Reply $reply,
        Ticket $ticket,
        Settings $settings,
        TagRepository $tagRepository,
        ReplyRepository $replyRepository
    )
    {
        $this->tag = $tag;
        $this->reply = $reply;
        $this->ticket = $ticket;
        $this->settings = $settings;
        $this->tagRepository = $tagRepository;
        $this->replyRepository = $replyRepository;
    }

    /**
     * Find ticket by id.
     *
     * @param $id
     * @return Ticket|Collection
     */
    public function find($id)
    {
        return $this->ticket->find($id);
    }

    /**
     * Find ticket by id or fail.
     *
     * @throws ModelNotFoundException
     *
     * @param $id
     * @return Ticket
     */
    public function findOrFail($id)
    {
        return $this->ticket->findOrFail($id);
    }

    /**
     * Update tickets timestamps.
     *
     * @param Ticket $ticket
     * @return bool
     */
    public function touch(Ticket $ticket)
    {
        return $ticket->touch();
    }

    /**
     * Create a new ticket from specified data.
     *
     * @param array $data
     * @param bool  $markAsOpen
     *
     * @return Ticket
     */
    public function create($data, $markAsOpen = true)
    {
        if ( ! Arr::has($data, 'user_id')) $data['user_id'] = Auth::user()->id;

        /** @var Ticket $ticket */
        $ticket = $this->ticket->create(['user_id' => $data['user_id'], 'subject' => $data['subject']]);

        if ($markAsOpen) {
            $tagName = Arr::get($data, 'status', 'open');
            $this->tagRepository->attachByName($ticket, $tagName);
        }

        if (isset($data['category'])) {
            $this->tagRepository->attachById($ticket, $data['category']);
        }

        $reply = $this->replyRepository->create(Arr::only($data, ['body', 'user_id']), $ticket);

        if (isset($data['uploads']) && ! empty($data['uploads'])) {
            $reply->uploads()->sync($data['uploads'], false);
        }

        return $ticket;
    }

    /**
     * Assign tickets to given agent or unassign if no agent id given.
     *
     * @param array    $ticketsIds
     * @param int|null $agentId
     */
    public function assignToAgent($ticketsIds, $agentId = null)
    {
        $this->ticket
             ->whereIn('id', $ticketsIds)
             ->update(['assigned_to' => $agentId]);
    }

    /**
     * Load ticket matching given ID, as well as replies
     * and other relationships needed to display conversation.
     *
     * @param Ticket $ticket
     * @return Ticket
     */
    public function loadConversation(Ticket $ticket)
    {
        $ticket->load('tags.categories', 'user.purchase_codes', 'user.tags', 'assignee');

        $ticket->setRelation('replies', $this->replyRepository->getRepliesForTicket($ticket->id, 10));

        $ticket->replies->load('user', 'uploads')->each(function(Reply $reply) {
            $reply->created_at_formatted = $reply->created_at->diffForHumans();
        });

        $ticket->created_at_formatted = $ticket->created_at->diffForHumans();
        $ticket->created_at_month     = $ticket->created_at->formatLocalized('%B %d');

        return $ticket;
    }

    /**
     * Attach a tag to specified tickets.
     *
     * @param array $ticketIds
     * @param string|int $tagId
     */
    public function addTagToTickets($ticketIds, $tagId)
    {
        $rows = DB::table('taggables')->whereIn('taggable_id', $ticketIds)->where('tag_id', $tagId)->where('taggable_type', Ticket::class)->get();

        //remove ticket ids that already have specified tag attached
        foreach($rows as $existingRel) {
            $key = array_search($existingRel->taggable_id, $ticketIds);
            if ($key !== false) unset($ticketIds[$key]);
        }

        $data = array_map(function($id) use($tagId) {
            return ['tag_id' => $tagId, 'taggable_id' => $id, 'taggable_type' => Ticket::class];
        }, $ticketIds);

        DB::table('taggables')->insert($data);
    }

    /**
     * Remove given tag from tickets matching specified ids.
     *
     * @param int[] $ticketsIds
     * @param int $tagId
     * @return int
     */
    public function removeTagFromTickets($ticketsIds, $tagId)
    {
        return DB::table('taggables')
            ->whereIn('taggable_id', $ticketsIds)
            ->where('tag_id', $tagId)
            ->where('taggable_type', Ticket::class)
            ->delete();
    }

    /**
     * Change status of multiple tickets to given one.
     *
     * @param array $ticketIds
     * @param string $statusName
     */
    public function changeStatus($ticketIds, $statusName)
    {
        $tags = $this->tagRepository->getByType('status');
        $existing = $tags->pluck('id')->toArray();

        //find tag of status we should put ticket in
        $statusTag = $tags->first(function($tag) use($statusName)  {
            return $tag->name === $statusName;
        });

        if ( ! $statusTag) return;

        //remove existing status tags from tickets
        DB::table('taggables')
            ->whereIn('taggable_id', $ticketIds)
            ->whereIn('tag_id', $existing)
            ->where('taggable_type', Ticket::class)
            ->delete();

        //add new status tag to tickets
        $insert = collect($ticketIds)->map(function($id) use($statusTag) {
            return ['tag_id' => $statusTag->id, 'taggable_id' => $id, 'taggable_type' => Ticket::class];
        });

        DB::table('taggables')->insert($insert->toArray());

        //touch "update_at" timestamp for all tickets
        $this->ticket->whereIn('id', $ticketIds)->update([
            'updated_at' => $this->ticket->freshTimestamp(),
            'closed_at'  => $statusName === 'closed' ? Carbon::now() : null,
        ]);
    }


    /**
     * Change status of specified ticket to 'open'.
     *
     * @param Ticket $ticket
     * @return Tag|boolean
     */
    public function open(Ticket $ticket)
    {
        $newTag = $this->tagRepository->findByName('open');

        if ( ! $newTag) return false;

        $currentTag = $this->tagRepository->getByType('status', $ticket)->first();

        if ($currentTag) {

            //pending tickets are already 'open' so no need to replace pending tags for them
            if ($currentTag->name == 'pending' && $newTag->name == 'open') {
                return $currentTag;
            }

            $ticket->tags()->detach($currentTag->id);
        }

        $ticket->tags()->attach($newTag->id);

        return $newTag;
    }

    /**
     * Delete tickets matching given ids.
     *
     * @param array $ids
     */
    public function deleteTickets($ids)
    {
        $replyIds = $this->reply->whereIn('ticket_id', $ids)->get(['id', 'ticket_id'])->pluck('id');

        //detach uploads from replies
        DB::table('file_entry_models')->whereIn('model_id', $replyIds)->where('model_type', Reply::class)->delete();

        //detach tags from tickets
        DB::table('taggables')->whereIn('taggable_id', $ids)->where('taggable_type', Ticket::class)->delete();

        //delete tickets replies
        $this->reply->whereIn('id', $replyIds)->delete();

        //delete tickets
        $this->ticket->whereIn('id', $ids)->delete();
    }

    public function merge($ticket1, $ticket2)
    {
        $ticket = $this->findOrFail($ticket1);
        $mergee = $this->findOrFail($ticket2);

        //merge replies (without touching timestamps)
        DB::table('replies')->where('ticket_id', $mergee->id)->update(['ticket_id' => $ticket->id]);

        //merge tags and delete mergee ticket
        $tagIds = $ticket->tags->pluck('id')->merge($mergee->tags->pluck('id'));
        $this->deleteTickets([$mergee->id]);
        $ticket->tags()->sync($tagIds);

        return $this->loadConversation($ticket);
    }

    public function update(Ticket $ticket, $params)
    {
        $ticket->fill($params)->save();
        return $ticket;
    }
}
