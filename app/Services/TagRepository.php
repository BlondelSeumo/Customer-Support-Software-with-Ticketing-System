<?php namespace App\Services;

use App\Tag;
use App\Ticket;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

class TagRepository {

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @param Tag $tag
     * @param Ticket $ticket
     */
    public function __construct(Tag $tag, Ticket $ticket)
    {
        $this->tag = $tag;
        $this->ticket = $ticket;
    }

    /**
     * Find tag by id or throw error.
     *
     * @param integer $id
     * @throws ModelNotFoundException
     *
     * @return Tag
     */
    public function findOrFail($id)
    {
        return $this->tag->findOrFail($id);
    }

    /**
     * Return single or multiple tags matching given name(s).
     *
     * @param $name
     * @return Tag|Collection
     */
    public function findByName($name)
    {
        if (is_array($name)) {
            return $this->tag->whereIn('name', $name)->get();
        } else {
            return $this->tag->where('name', $name)->first();
        }
    }

    /**
     * Get all tags with category/status type and tickets count for each tag.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getStatusAndCategoryTags()
    {
        $activeTickets = $this->ticket->whereHas('tags', function(Builder $q) {
            $q->where('name', 'open');
        })->select(['id', 'assigned_to'])->with(['tags' => function(MorphToMany $q) {
            $q->select('id');
        }])->limit(100)->get();

        $unassigned = 'unassigned';
        $mine = 'mine';
        $assigned = 'assigned';
        $closed = 'closed';

        $viewTags = collect([
            [
                'name' => $unassigned,
                'id' => $unassigned,
                'display_name' => ucfirst($unassigned),
                'type' => 'view',
                'tickets_count' => $activeTickets->filter(function(Ticket $ticket) {
                    return !$ticket->assigned_to;
                })->count(),
            ],
            [
                'id' => $mine,
                'name' => $mine,
                'type' => 'view',
                'display_name' => ucfirst($mine),
                'tickets_count' => $activeTickets->filter(function(Ticket $ticket) {
                    return $ticket->assigned_to === Auth::id();
                })->count(),
            ],
            [
                'name' => $assigned,
                'id' => $assigned,
                'display_name' => ucfirst($assigned),
                'type' => 'view',
                'tickets_count' => $activeTickets->filter(function(Ticket $ticket) {
                    return !!$ticket->assigned_to && $ticket->assigned_to !== Auth::id();
                })->count(),
            ],
            [
                'name' => $closed,
                'id' => $closed,
                'display_name' => ucfirst($closed),
                'type' => 'view',
            ],
        ]);

        $statusTags = $this->tag->where('type', 'status')->get();

        $categoryTags = $this->tag->where('type', 'category')
            ->whereHas('tickets')
            ->with('categories')
            ->get()
            ->map(function(Tag $tag) use($activeTickets) {
                $tag->tickets_count = $activeTickets->filter(function(Ticket $ticket) use($tag) {
                    return $ticket->tags->contains($tag);
                })->count();
                return $tag;
            });

        return $viewTags->merge($statusTags)->merge($categoryTags)->values();
    }

    /**
     * Find tags of specified type.
     *
     * @param string $type
     * @param Model $taggable
     *
     * @return Collection
     */
    public function getByType($type, $taggable = null)
    {
        if ($taggable) {
            return $taggable->tags()->where('type', $type)->get();
        } else {
            return $this->tag->where('type', $type)->get();
        }
    }

    /**
     * Attach tag matching given name to ticketID.
     * 
     * @param Ticket $ticket
     * @param string $tagName
     */
    public function attachByName(Ticket $ticket, $tagName)
    {
        $tag = $this->findByName($tagName);

        if ( ! $tag) return;

        $this->attachById($ticket, $tag->id);
    }

    /**
     * Attach specified tags to given ticket.
     *
     * @param Ticket $ticket
     * @param array|int $tagIds
     */
    public function attachById(Ticket $ticket, $tagIds)
    {
        if ( ! is_array($tagIds)) $tagIds = [$tagIds];

        $alreadyAttached = $ticket->tags->pluck('id')->toArray();

        $ticket->tags()->attach(
            array_diff($tagIds, $alreadyAttached)
        );
    }

    /**
     * Detach specified tags from all taggables or only specified one.
     *
     * @param array|int $tagIds
     * @param Model|null $taggable
     */
    public function detachById($tagIds, Model $taggable = null)
    {
        if ( ! is_array($tagIds)) $tagIds = [$tagIds];

        $query = DB::table('taggables')->whereIn('tag_id', $tagIds);

        if ($taggable) {
            $query->where('taggable_id', $taggable->id)
                ->where('taggable_type', get_class($taggable));
        }

        $query->delete();
    }

    /**
     * Create a new tag.
     *
     * @param array $data
     * @return Tag
     */
    public function create($data)
    {
        $tag = $this->tag->forceCreate([
            'name' => $data['name'],
            'type' => isset($data['type']) ? $data['type'] : 'custom',
            'display_name' => isset($data['display_name']) ? $data['display_name'] : $data['name'],
        ]);

        $this->syncTaggables($tag, Arr::get($data, 'taggables'));

        return $tag;
    }

    /**
     * Update existing tag.
     *
     * @param Tag   $tag
     * @param array $data
     * @return Tag
     */
    public function update(Tag $tag, $data)
    {
        $tag->fill(Arr::except($data, 'taggables'))->save();
        $this->syncTaggables($tag, Arr::get($data, 'taggables'));

        return $tag;
    }

    /**
     * @param Tag $tag
     * @param array|null $taggables
     * @return Tag
     */
    private function syncTaggables(Tag $tag, $taggables)
    {
        if ($taggables) {
            $taggableIds = array_map(function($taggable) use($tag) {
                return ['taggable_id' => $taggable['id']];
            }, $taggables);
            $relation = $taggables[0]['relation'];
            $tag->$relation()->sync($taggableIds);
        }
        return $tag;
    }

    /**
     * Create new tag or update existing one with specified data.
     *
     * @param array $data
     * @return Tag
     */
    public function updateOrCreate($data)
    {
        return $this->tag->updateOrCreate(['name' => $data['name']], [
            'type' => isset($data['type']) ? $data['type'] : 'custom',
            'display_name' => isset($data['display_name']) ? $data['display_name'] : $data['name'],
        ]);
    }

    /**
     * Delete multiple tags and detach them from all taggables.
     */
    public function deleteMultiple($ids)
    {
        $this->tag->whereIn('id', $ids)->delete();
        $this->detachById($ids);
    }

    /**
     * Get validation rules for specified method.
     *
     * @param string $type
     * @param int    $tagId
     * @return array
     */
    public function getValidationRules($type = 'store', $tagId = null)
    {
        $rules = [
            'name' => 'required|string|between:2,80|unique:tags,name',
            'type' => 'required|string|in:status,custom,category',
            'display_name' => 'min:2|max:255|unique:tags,display_name'
        ];

        //make sure validation errors are not thrown if we're trying to update
        //this tags name or display name this the same value as current one
        if ($tagId) {
            $rules['name'].=",$tagId";
            $rules['display_name'].=",$tagId";
        }

        //remove 'required' validation if we're not getting rules for creating new tag
        if ($type !== 'store') {
            $rules = array_map(function($rule) { return str_replace('required|', '', $rule); }, $rules);
        }

        return $rules;
    }
}
