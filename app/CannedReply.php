<?php namespace App;

use Carbon\Carbon;
use Common\Files\FileEntry;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\belongsToMany;

/**
 * App\CannedReply
 *
 * @property integer $id
 * @property string $name
 * @property string $body
 * @property integer $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|FileEntry[] $uploads
 * @property-read User $user
 * @mixin Eloquent
 * @property bool $shared
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply whereShared($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply whereUserId($value)
 * @property-read int|null $uploads_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CannedReply query()
 */
class CannedReply extends Model
{
    /**
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'shared' => 'boolean'];

    /**
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @return belongsToMany
     */
    public function uploads()
    {
        return $this->morphToMany(FileEntry::class, 'model', 'file_entry_models')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Many to one relationship with user model.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
