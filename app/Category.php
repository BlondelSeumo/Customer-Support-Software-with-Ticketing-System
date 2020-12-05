<?php namespace App;

use App\Traits\OrdersByPosition;
use Carbon\Carbon;
use Common\Settings\Settings;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Category
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property integer $position
 * @method static \Illuminate\Database\Query\Builder|Category orderByPosition()
 * @mixin Eloquent
 * @property bool $default
 * @method static \Illuminate\Database\Query\Builder|Category whereDefault($value)
 * @property int $parent_id
 * @property bool $hidden
 * @property-read Collection|Article[] $articles
 * @property-read Collection|Category[] $children
 * @property-read Category $parent
 * @method static \Illuminate\Database\Query\Builder|Category rootOnly()
 * @property string|null $image
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category whereUpdatedAt($value)
 * @property-read int|null $articles_count
 * @property-read int|null $children_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Category query()
 */
class Category extends Model
{
    use OrdersByPosition;

    protected $guarded = ['id'];

    protected $casts = [
        'id'        => 'integer',
        'default'   => 'boolean',
        'parent_id' => 'integer',
        'position'  => 'integer',
        'hidden'    => 'boolean',
    ];

    /**
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderByPosition();
    }

    /**
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return BelongsToMany
     */
    public function articles()
    {
        $query = $this->belongsToMany(Article::class, 'category_article')
            ->where('draft', false);

        list($col, $dir) = explode('|', app(Settings::class)->get('articles.default_order', 'position|desc'));
        $col === 'position' ? $query->orderByPosition() : $query->orderBy($col, $dir);

        return $query;
    }

    /**
     * Filter out child categories and only return root ones.
     *
     * @param $query
     * @return Builder
     */
    public function scopeRootOnly($query) {
        return $query->whereNull('parent_id');
    }
}
