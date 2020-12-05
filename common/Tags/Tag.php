<?php

namespace Common\Tags;

use Carbon\Carbon;
use Common\Files\FileEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

class Tag extends Model
{
    protected $hidden = ['pivot'];
    protected $guarded = ['id'];
    protected $casts = ['id' => 'integer'];

    const DEFAULT_TYPE = 'default';

    /**
     * @return MorphToMany
     */
    public function files()
    {
        return $this->morphedByMany(FileEntry::class, 'taggable');
    }

    /**
     * @param array $ids
     * @param null|int $userId
     */
    public function attachEntries($ids, $userId = null)
    {
        if ($userId) {
            $ids = collect($ids)->mapWithKeys(function($id) use($userId) {
                return [$id => ['user_id' => $userId]];
            });
        }

        $this->files()->syncWithoutDetaching($ids);
    }

    /**
     * @param array $ids
     * @param null|int $userId
     */
    public function detachEntries($ids, $userId = null)
    {
        $query = $this->files();

        if ($userId) {
            $query->wherePivot('user_id', $userId);
        }

        $query->detach($ids);
    }

    /**
     * @param Collection|array $tags
     * @param string $type
     * @return Collection|Tag[]
     */
    public function insertOrRetrieve($tags, $type = 'custom')
    {
        if ( ! $tags instanceof Collection) {
            $tags = collect($tags);
        }

        $tags = $tags->filter();

        if (is_string($tags->first())) {
            $tags = $tags->map(function($tag) {
                return ['name' => $tag];
            });
        }

        $existing = $this->getByNames($tags->pluck('name'), $type);

        $new = $tags->filter(function($tag) use($existing) {
            return !$existing->first(function($existingTag) use($tag) {
                return slugify($existingTag['name']) === slugify($tag['name']);
            });
        });

        if ($new->isNotEmpty()) {
            $new->transform(function($tag) use($type) {
                $tag['created_at'] = Carbon::now();
                $tag['updated_at'] = Carbon::now();
                $tag['type'] = $type;
                return $tag;
            });
            $this->insert($new->toArray());
            return $this->getByNames($tags->pluck('name'), $type);
        } else {
            return $existing;
        }
    }

    /**
     * @param Collection|string[] $names
     * @param string $type
     * @return Collection
     */
    public function getByNames(Collection $names, $type = null)
    {
        // don't try to slugify or lowercase tag names
        // as that will cause duplicate tags in many cases.
        // for example "action & adventure" and "action adventure"
        $query = $this->whereIn('name', $names);
        if ($type) {
            $query->where('type', $type);
        }
        return $query->get();
    }

    /**
     * @param string $value
     * @return string
     */
    public function getDisplayNameAttribute($value)
    {
        return $value ? $value : $this->attributes['name'];
    }
}
