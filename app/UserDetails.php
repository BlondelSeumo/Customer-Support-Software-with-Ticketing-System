<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserDetails
 *
 * @property integer $id
 * @property string $name
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @mixin \Eloquent
 * @property string $details
 * @property string $notes
 * @property int $user_id
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDetails whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDetails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDetails whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserDetails whereUserId($value)
 */
class UserDetails extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['details', 'notes'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * User profile belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
