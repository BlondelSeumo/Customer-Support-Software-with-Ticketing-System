<?php namespace App;

use Carbon\Carbon;
use Common\Files\FileEntry;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\belongsToMany;
use Illuminate\Support\Str;

/**
 * App\Reply
 *
 * @property integer $id
 * @property string $body
 * @property integer $user_id
 * @property integer $ticket_id
 * @property string $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string created_at_formatted
 * @property-read Collection|FileEntry[] $uploads
 * @property-read Ticket $ticket
 * @property-read User $user
 * @method static \Illuminate\Database\Query\Builder|Reply compact()
 * @mixin Eloquent
 * @property string $uuid
 * @method static \Illuminate\Database\Query\Builder|Reply whereUuid($value)
 * @property-read mixed $created_at_formatted
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply whereUserId($value)
 * @property-read int|null $uploads_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Reply query()
 */
class Reply extends Model
{
    const DRAFT_TYPE = 'drafts';
    const REPLY_TYPE = 'replies';
    const NOTE_TYPE = 'notes';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'ticket_id' => 'integer',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'animated'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['uuid'];

    /**
     * @return belongsToMany
     */
    public function uploads()
    {
        return $this->morphToMany(FileEntry::class, 'model', 'file_entry_models')
            ->orderBy('file_entries.created_at', 'desc');
    }

    /**
     * @return belongsTo
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompact(Builder $q)
    {
        return $q->select('id', 'user_id', DB::raw('SUBSTRING(body, 1, 80) as body'));
    }

    /**
     * @param int $length
     */
    public function stripBody($length = 200)
    {
        if ($this->exists) {
            $body = Str::limit(strip_tags($this->body, '<br>'), $length);
            $this->body = preg_replace('/<br\W*?>/', ' ', $body); // replace <br> with space
        }
    }

    public function bodyForEmail()
    {
        // prepend relative image urls for email body
        return preg_replace('/"\/?(storage\/ticket_images\/[a-zA-Z0-9]+.[a-z]+)"/', url('') . "/$1", $this->body);
    }

    public function getCreatedAtFormattedAttribute()
    {
        return (new Carbon($this->attributes['created_at']))->formatLocalized('%b %e, %H:%M');
    }
}
