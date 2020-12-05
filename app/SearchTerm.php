<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\SearchTerm
 *
 * @property int $id
 * @property string $term
 * @property \Carbon\Carbon|null $created_at
 * @property string $normalized_term
 * @property int $result_count
 * @property int $clicked_article
 * @property int $created_ticket
 * @property string $source
 * @property string|null $page
 * @property int|null $category_id
 * @property int|null $user_id
 * @property-read \App\Category|null $category
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereClickedArticle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereCreatedTicket($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereNormalizedTerm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereResultCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereTerm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SearchTerm whereUserId($value)
 * @mixin \Eloquent
 */
class SearchTerm extends Model
{
    /**
     * @var array
     */
    protected $guarded = ['id'];

    const UPDATED_AT = null;

    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
