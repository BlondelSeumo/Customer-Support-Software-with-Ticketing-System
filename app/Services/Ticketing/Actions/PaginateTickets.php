<?php

namespace App\Services\Ticketing\Actions;

use App\Ticket;
use Arr;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use Str;

class PaginateTickets
{

    public function execute(array $params)
    {
        /** @var $query Ticket */
        $query = Ticket::join('taggables', 'taggables.taggable_id', '=', 'tickets.id')
            ->where('taggables.taggable_type', Ticket::class)
            ->join('tags', function(JoinClause $join) {
                $join->on('tags.id', '=', 'taggables.tag_id');
                $join->on('tags.type', '=', DB::raw("'status'"));
            })->select('tickets.*', 'tags.name as status')
            ->distinct()
            ->with(['user', 'tags', 'latest_reply', 'assignee'])
            ->withCount('replies');

        $tagId      = $params['tagId'] ?? null;
        $assignee   = $params['assigned_to'] ?? null;
        $requester  = $params['userId'] ?? null;
        $searchTerm = $params['query'] ?? null;
        $perPage    = $params['perPage'] ?? 15;
        $page       = $params['page'] ?? 1;

        // if tag id is "mine" we need to get tickets assigned to current user
        if ($tagId) {
            $this->filterByTag($tagId, $query);
        }

        // filter by assignee
        if ($assignee) {
            $query->where('assigned_to', $assignee);
        }

        // get only tickets that specified user has created
        if ($requester) {
            $query->where('tickets.user_id', $requester);
        }

        if ($searchTerm) {
            $query->where('subject', 'like', "$searchTerm%");
        }

        $total = $query->newQuery()->count('tickets.id');

        if ($orderBy = Arr::get($params, 'orderBy')) {
            $query->orderBy($orderBy, Arr::get($params, 'orderDir', 'desc'));
        } else {
            $query->orderByStatus();
        }

        $items = $query->forPage($page, $perPage)->get();

        //remove html tags from replies and limit to 1 reply
        $items->each(function($ticket) {
            if ($ticket->latest_reply) {
                $ticket->latest_reply->body = Str::limit(strip_tags($ticket->latest_reply->body), 300);
            }
        });

        return new LengthAwarePaginator($items, $total, $perPage);
    }

    /**
     * @param string|number $tagId
     * @param Builder $builder
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function filterByTag($tagId, Builder $builder)
    {
        switch ($tagId) {
            case 'unassigned':
                return $builder->whereNull('assigned_to');
            case 'mine':
                return $builder->where('assigned_to', Auth::id());
            case 'assigned':
                return $builder->whereNotNull('assigned_to');
            case 'closed':
                return $builder->whereNotNull('closed_at');
            default:
                return $builder->whereHas('tags', function(Builder $query) use($tagId) {
                    $query->where('tags.id', (int) $tagId)->orWhere('tags.name', (string) $tagId);
                });
        }
    }

}
