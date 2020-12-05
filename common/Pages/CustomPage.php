<?php namespace Common\Pages;

use App\User;
use Carbon\Carbon;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Str;

/**
 * App\Page
 *
 * @property int $id
 * @property string $body
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int user_id
 * @mixin Eloquent
 */
class CustomPage extends Model
{
    const PAGE_TYPE = 'default';

    protected $guarded = ['id'];

    protected $casts = [
        'hide_nav' => 'boolean',
    ];

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = slugify($value);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
