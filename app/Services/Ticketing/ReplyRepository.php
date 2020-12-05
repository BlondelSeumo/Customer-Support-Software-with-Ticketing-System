<?php namespace App\Services\Ticketing;

use Auth;
use App\Reply;
use App\Ticket;
use Common\Files\FileEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ReplyRepository {

    /**
     * Reply model.
     *
     * @var Reply
     */
    private $reply;

    /**
     * Upload model.
     *
     * @var FileEntry
     */
    private $fileEntry;

    /**
     * ReplyRepository constructor.
     *
     * @param Reply $reply
     * @param FileEntry $upload
     */
    public function __construct(Reply $reply, FileEntry $upload)
    {
        $this->reply  = $reply;
        $this->fileEntry = $upload;
    }

    /**
     * Find reply by specified id.
     *
     * @param int $id
     *
     * @return Reply
     */
    public function findOrFail($id)
    {
        return $this->reply->findOrFail($id);
    }

    /**
     * Find reply matching specified UUID.
     *
     * @param string $uuid
     *
     * @return Reply
     */
    public function findByUuid($uuid)
    {
        return $this->reply->where('uuid', $uuid)->first();
    }

    /**
     * Paginate all replies of specified ticket.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $query = $this->getRepliesIndexQuery($params['ticket_id']);

        $paginated = $query->paginate(isset($params['perPage']) ? $params['perPage'] : 10);

        return $paginated;
    }

    /**
     * Fetch replies for specified ticket.
     *
     * @param int $ticketId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRepliesForTicket($ticketId, $limit = 10)
    {
        return $this->getRepliesIndexQuery($ticketId)->limit($limit)->get();
    }

    /**
     * Delete specified reply and detach uploads from it.
     *
     * @param Reply $reply
     */
    public function delete(Reply $reply)
    {
        $ids = $reply->uploads()->pluck('file_entries.id')->toArray();

        $this->detachUploads($reply, $ids);

        $reply->delete();
    }

    /**
     * Detach specified uploads from reply.
     *
     * @param Reply $reply
     * @param $uploads
     *
     * @return int
     */
    public function detachUploads(Reply $reply, $uploads)
    {
        return $reply->uploads()->detach($uploads);
    }

    /**
     * Update specified reply or note.
     *
     * @param array $data
     * @param int|Reply $reply
     *
     * @return Reply
     */
    public function update($data, $reply)
    {
        if ( ! is_a($reply, Reply::class)) {
            $reply = $this->reply->findOrFail($reply);
        }

        if (isset($data['body'])) {
            $reply->body = $data['body'];
        }

        if (isset($data['uploads'])) {
            $reply->uploads()->sync($data['uploads']);
        }

        $reply->save();

        return $reply->load('uploads', 'user');
    }

    /**
     * Create a new reply of any type.
     *
     * @param array  $data
     * @param Ticket $ticket
     * @param string $type
     *
     * @return Reply
     */
    public function create($data, Ticket $ticket, $type = Reply::REPLY_TYPE)
    {
        $reply = $this->reply->create([
            'body'      => isset($data['body']) ? $data['body'] : '',
            'user_id'   => isset($data['user_id']) ? $data['user_id'] : Auth::user()->id,
            'ticket_id' => $ticket->id,
            'type'      => $type,
            'uuid'      => Str::random(30),
        ]);

        if ( ! empty($data['uploads'])) {
            $reply->uploads()->attach($data['uploads']);
        }

        return $reply;
    }

    /**
     * Get Eloquent query for loading replies for given ticket.
     *
     * @param int $ticketId
     * @return Builder
     */
    private function getRepliesIndexQuery($ticketId)
    {
        $query = $this->reply->with('user', 'uploads')->where('ticket_id', $ticketId);

        $query->where(function(Builder $replyQuery) {

            //load all replies
            $replyQuery->where('type', Reply::REPLY_TYPE);

            //load only drafts current user has created
            $replyQuery->orWhere(function(Builder $draftQuery) {
                $draftQuery->where('type', Reply::DRAFT_TYPE)->where('user_id', Auth::user()->id);
            });

            //load notes if current user is agent
            if (Auth::user()->isAgent()) {
                $replyQuery->orWhere('type', Reply::NOTE_TYPE);
            };
        });

        return $query->orderBy('created_at', 'desc');
    }
}
