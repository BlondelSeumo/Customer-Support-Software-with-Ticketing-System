<?php namespace App\Policies;

use App\User;
use App\Reply;
use App\Ticket;
use Common\Core\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReplyPolicy extends BasePolicy
{
    public function show(User $user, Reply $reply) {
        $replyBelongsToUserTicket = ($reply->ticket && $reply->ticket->user_id === $user->id) && $reply->type === Reply::REPLY_TYPE;
        return $replyBelongsToUserTicket || $reply->user_id === $user->id || $user->hasPermission('replies.view');
    }

    public function index(User $user, Ticket $ticket) {
        return $ticket->user_id === $user->id || $user->hasPermission('replies.view') || $user->hasPermission('tickets.view');
    }

    public function store(User $user, Ticket $ticket) {
        return $ticket->user_id === $user->id || $user->hasPermission('replies.create');
    }

    public function update(User $user, Reply $reply) {
        $isDraft = $reply && $reply->user_id === $user->id && $reply->type === Reply::DRAFT_TYPE;
        return $isDraft || $user->hasPermission('replies.update');
    }

    public function destroy(User $user, Reply $reply, $type = null) {
        if ($user->hasPermission('replies.delete')) {
            return true;
        }

        //if draft type is specified we should only
        //allow current user drafts to be deleted.
        if ($type === Reply::DRAFT_TYPE) {
            return $reply->type === Reply::DRAFT_TYPE && $user->id == $reply->user_id;
        }
    }
}
