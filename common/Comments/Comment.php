<?php

namespace Common\Comments;

use App\User;
use Common\Files\Traits\HandlesEntryPaths;
use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Comment
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string content
 * @method Comment rootOnly()
 * @method Comment childrenOnly()
 * @mixin Eloquent
 */
class Comment extends Model
{
    use HandlesEntryPaths;

    protected $guarded = ['id'];

    protected $hidden = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'created_at',
        'updated_at',
        'path',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer'
    ];

    protected $appends = ['depth'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeRootOnly(Builder $builder)
    {
        return $builder->whereNull('parent_id');
    }

    public function scopeChildrenOnly(Builder $builder)
    {
        return $builder->whereNotNull('parent_id');
    }

    public function getDepthAttribute()
    {
        return substr_count($this->getRawOriginal('path'), '/');
    }
}
