<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ArticleFeedback
 *
 * @property integer $id
 * @property boolean $was_helpful
 * @property string $comment
 * @property integer $hc_article_id
 * @property integer $user_id
 * @property string $ip
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Article $article
 * @method static \Illuminate\Database\Query\Builder|\App\ArticleFeedback whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ArticleFeedback whereWasHelpful($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ArticleFeedback whereComment($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ArticleFeedback whereArticleId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ArticleFeedback whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ArticleFeedback whereIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ArticleFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ArticleFeedback whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $article_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ArticleFeedback query()
 */
class ArticleFeedback extends Model
{
    protected $guarded = ['id'];

    protected $table = 'article_feedback';

    protected $casts = ['was_helpful' => 'integer'];

    /**
     * Help center article this feedback belongs to.
     */
    public function article()
    {
        return $this->belongsTo('App\Article');
    }
}
