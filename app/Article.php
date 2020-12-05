<?php namespace App;

use App\Services\HelpCenter\AddIdToAllHtmlHeadings;
use Carbon\Carbon;
use Common\Files\FileEntry;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Scout\Searchable;
use App\Traits\OrdersByPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Article
 *
 * @property integer $id
 * @property string $title
 * @property string $body
 * @property string $extra_data
 * @property boolean $draft
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $visibility
 * @property integer $views
 * @property-read Collection|Tag[] $tags
 * @property-read Collection|ArticleFeedback[] $feedback
 * @method static \Illuminate\Database\Query\Builder|Article filterByCategories($ids)
 * @method static \Illuminate\Database\Query\Builder|Article withCategories($categories)
 * @method static \Illuminate\Database\Query\Builder|Article orderByFeedback($direction = 'desc')
 * @mixin Eloquent
 * @property string $slug
 * @property string $description
 * @property-read Collection|Category[] $categories
 * @property int $position
 * @method static \Illuminate\Database\Query\Builder|Article filterByTags($names)
 * @method static \Illuminate\Database\Query\Builder|Article orderByPosition()
 * @property-read Collection|FileEntry[] $uploads
 * @property-read int|null $categories_count
 * @property-read int|null $feedback_count
 * @property-read int|null $tags_count
 * @property-read int|null $uploads_count
 */
class Article extends Model
{
    use Searchable, OrdersByPosition;

    /**
     * @return array
     */
    public function toSearchableArray()
    {
        $array = [
            'id' => $this->id,
            'title' => $this->title,
            'body' => strip_tags($this->body),
            'description' => $this->description,
        ];

        $array['tags'] = $this->tags->pluck('display_name');

        if (config('scout.driver') === 'tntsearch') {
            $array['tags'] = $array['tags']->implode(',');
        }

        return $array;
    }

    /**
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * @var array
     */
    public $orderFields = ['title', 'draft', 'visibility', 'views', 'was_helpful', 'created_at', 'updated_at', 'position'];

    /**
     * @var array
     */
    protected $casts = ['id' => 'integer', 'was_helpful' => 'integer', 'position' => 'integer'];

    public function setBodyAttribute($value)
    {
        if ($value) {
            $this->attributes['body'] = app(AddIdToAllHtmlHeadings::class)->execute($value);
        }
    }

    /**
     * Help center categories that this article is attached to.
     */
    public function categories()
    {
        return $this->belongsToMany('App\Category', 'category_article', 'article_id', 'category_id')->orderBy('parent_id', 'desc');
    }

    /**
     * Tags attached to this article.
     *
     * @return MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany('App\Tag', 'taggable');
    }

    /**
     * @return belongsToMany
     */
    public function uploads()
    {
        return $this->morphToMany(FileEntry::class, 'model', 'file_entry_models')
            ->orderBy('file_entries.created_at', 'desc');
    }

    /**
     * User feedback attach to this article.
     *
     * @return HasMany
     */
    public function feedback()
    {
        return $this->hasMany(ArticleFeedback::class);
    }

    /**
     * Search articles using basic mysql LIKE query.
     *
     * @param $query
     * @return Builder
     */
    public function basicSearch($query)
    {
        return $this->where('title', 'like', "%$query%")
            ->orWhere('body', 'like', "%$query%")
            ->orWhereHas('tags', function($q) use ($query) {
                return $q->where('name', 'like', "%$query%")
                    ->orWhere('display_name', 'like', "%$query%");
            });
    }

    /**
     * @param Builder $query
     * @param mixed $ids
     * @return mixed
     */
    public function scopeFilterByCategories($query, $ids)
    {
        if ( ! is_array($ids)) $ids = explode(',', $ids);

        return $query->whereHas('categories', function($q) use($ids) {
            $q->whereIn('categories.id', $ids);
        });
    }

    /**
     * @param Builder $query
     * @param mixed $names
     * @return mixed
     */
    public function scopeFilterByTags($query, $names)
    {
        if ( ! is_array($names)) $names = explode(',', $names);
        return $query->whereHas('tags', function($q) use($names) {
            $q->whereIn('tags.name', $names);
        });
    }

    /**
     * @param Builder $query
     * @param string|number[]|null $categories
     * @return mixed
     */
    public function scopeWithCategories($query, $categories)
    {
        return $query->with(['categories' => function(BelongsToMany $q) use($categories) {
            $q->with('parent');
            if ($categories) {
                $q->whereHas('parent', function(Builder $q) use($categories) {
                    if ( ! is_array($categories)) $categories = explode(',', $categories);
                    $q->whereIn('id', $categories);
                });
            }
        }]);
    }

    /**
     * Order articles by the amount of 'was helpful' user
     * feedback they have in hc_article_feedback table.
     *
     * @param Builder $query
     * @param string  $direction
     * @return mixed
     */
    public function scopeOrderByFeedback($query, $direction = 'desc')
    {
        $prefix = DB::getTablePrefix();
        $subQuery= "(SELECT count(*) FROM {$prefix}article_feedback WHERE was_helpful = 1 AND article_id = {$prefix}articles.id) as was_helpful";

        return $query->select('*', DB::raw($subQuery))->orderBy('was_helpful', $direction);
    }
}
