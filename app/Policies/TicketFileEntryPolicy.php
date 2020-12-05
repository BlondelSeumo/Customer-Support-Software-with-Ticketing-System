<?php

namespace App\Policies;

use App\Reply;
use App\Ticket;
use App\User;
use Common\Core\Policies\FileEntryPolicy;
use Common\Files\FileEntry;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\Request;

class TicketFileEntryPolicy extends FileEntryPolicy
{
    use HandlesAuthorization;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Reply
     */
    private $reply;
    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @param Request $request
     * @param Reply $reply
     */
    public function __construct(Request $request, Reply $reply)
    {
        $this->request = $request;
        $this->reply = $reply;
    }

    public function show(User $user, FileEntry $entry, Reply $reply = null): bool
    {
        // user can view this file, if file is attached to user's ticket
        if ($this->request->get('ticketEntry')) {
            $fileEntryModel = \DB::table('file_entry_models')
                ->where('file_entry_id', $entry->id)
                ->where('model_type', Reply::class)
                ->first();

            if ( ! is_null($fileEntryModel)) {
                $reply = $this->reply->with('ticket')->find($fileEntryModel->model_id);
                if ($reply->ticket->user_id === $user->id) return true;
            }
        }

        return parent::show($user, $entry);
    }

    /**
     * Get shareable link for current request.
     *
     * @param Reply|null $reply
     * @return Reply|null
     */
    private function getReplyForRequest(Reply $reply = null) {
        if ($reply) return $reply;

        if ($this->request->filled('replyId')) {
            return $this->reply->findOrFail($this->request->get('shareable_link'));
        }

        return null;
    }
}
