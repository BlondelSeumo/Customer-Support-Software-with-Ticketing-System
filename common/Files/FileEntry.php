<?php namespace Common\Files;

use App\User;
use Auth;
use Carbon\Carbon;
use Common\Files\Traits\HandlesEntryPaths;
use Common\Files\Traits\HashesId;
use Common\Tags\HandlesTags;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Arr;
use Storage;

/**
 * FileEntry
 *
 * @mixin Eloquent
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property string $file_name
 * @property string $file_size
 * @property string $mime
 * @property string $extension
 * @property boolean $thumbnail
 * @property string $preview_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $type
 * @property-read FileEntry|null $parent
 * @property-read Collection $users
 * @property-read Collection $owner
 * @property string $path
 * @property string $disk_prefix
 * @property boolean $public
 * @method static \Illuminate\Database\Query\Builder|FileEntry onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|FileEntry rootOnly()
 * @method static \Illuminate\Database\Query\Builder|FileEntry onlyStarred()
 * @method static \Illuminate\Database\Query\Builder|FileEntry whereRootOrParentNotTrashed()
 * @method whereOwner($userId)
 */
class FileEntry extends Model
{
    use SoftDeletes, HashesId, HandlesEntryPaths, HandlesTags;

    protected $guarded = ['id'];
    protected $hidden  = ['pivot', 'preview_token'];
    protected $appends = ['hash', 'url'];
    protected $casts = [
        'id' => 'integer',
        'file_size' => 'integer',
        'user_id' => 'integer',
        'parent_id' => 'integer',
        'thumbnail' => 'boolean',
        'public' => 'boolean',
        'workspace_id' => 'integer',
    ];

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->morphedByMany(FileEntryUser::class, 'model', 'file_entry_models', 'file_entry_id', 'model_id')
            ->using(FileEntryPivot::class)
            ->select('first_name', 'last_name', 'email', 'users.id', 'avatar')
            ->withPivot('owner', 'permissions')
            ->withTimestamps()
            ->orderBy('file_entry_models.created_at', 'asc');
    }

    /**
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id')->withoutGlobalScope('fsType');
    }

    /**
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * @return BelongsToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->wherePivot('user_id', Auth::user()->id);
    }

    /**
     * @param string $value
     * @return string
     */
    public function getUrlAttribute($value)
    {
        if ($value) return $value;
        if ( ! isset($this->attributes['type']) || $this->attributes['type'] === 'folder') {
            return null;
        }

        if (Arr::get($this->attributes, 'public')) {
            return Storage::disk('public')->url("$this->disk_prefix/$this->file_name");
        } else {
            return 'secure/uploads/'.$this->attributes['id'];
        }
    }

    /**
     * @param bool $useThumbnail
     * @return string
     */
    public function getStoragePath($useThumbnail = false)
    {
        $fileName = $useThumbnail ? 'thumbnail.jpg' : $this->file_name;
        if ($this->public) {
            return "$this->disk_prefix/$fileName";
        } else {
            return "$this->file_name/$fileName";
        }
    }

    public function getDisk()
    {
        if ($this->public) {
            return Storage::drive('public');
        } else {
            return Storage::drive('uploads');
        }
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeWhereRootOrParentNotTrashed(Builder $query)
    {
        return $query->whereNull('parent_id')
            ->orWhereHas('parent', function(Builder $query) {
                return $query->whereNull('deleted_at');
            });
    }

    /**
     * @return BelongsToMany
     */
    public function owner()
    {
        return $this->users()->wherePivot('owner', true);
    }

    /**
     * @return User
     */
    public function getOwner() {
        return $this->owner->first();
    }

    /**
     * Select all entries user has access to.
     *
     * @param Builder $builder
     * @param $userId
     * @param bool|null $owner
     * @return Builder
     */
    public function scopeWhereUser(Builder $builder, $userId, $owner = null) {
        return $builder->whereIn('id', function ($query) use($userId, $owner) {
            $query->select('file_entry_id')
                ->from('file_entry_models')
                ->where('model_id', $userId)
                ->where('model_type', User::class);

            // if $owner is not null, need to load either only
            // entries user owns or entries user does not own
            //if $owner is null, load all entries
            if ( ! is_null($owner)) {
                $query->where('owner', $owner);
            }
        });
    }

    /**
     * Select only entries that were created by specified user.
     *
     * @param Builder $builder
     * @param $userId
     * @return Builder
     */
    public function scopeWhereOwner(Builder $builder, $userId) {
        return $this->scopeWhereUser($builder, $userId, true);
    }

    /**
     * Select only entries that were not created by specified user.
     *
     * @param Builder $builder
     * @param $userId
     * @return Builder
     */
    public function scopeWhereNotOwner(Builder $builder, $userId) {
        return $this->scopeWhereUser($builder, $userId, false);
    }

    /**
     * Get path of specified entry.
     *
     * @param int $id
     * @return string
     */
    public function findPath($id)
    {
        $entry = $this->find($id, ['path']);
        return $entry ? $entry->getRawOriginal('path') : '';
    }

    /**
     * Return file entry name with extension.
     * @return string
     */
    public function getNameWithExtension() {
        if ( ! $this->exists) return '';

        $extension = pathinfo($this->name, PATHINFO_EXTENSION);

        if ( ! $extension && $this->extension) {
            return $this->name .'.'. $this->extension;
        }

        return $this->name;
    }

    public function getTotalSize(): int
    {
        if ($this->type === 'folder') {
            return $this->allChildren()->sum('file_size');
        } else {
            return $this->file_size;
        }
    }
}
