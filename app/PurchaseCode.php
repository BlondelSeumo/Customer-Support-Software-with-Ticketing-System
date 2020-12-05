<?php namespace App;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\EnvatoPurchaseCode
 *
 * @property int $id
 * @property string $code
 * @property int $user_id
 * @property string $item_name
 * @property string $item_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @mixin Eloquent
 * @property string $url
 * @property string $image
 * @property Carbon $supported_until
 * @property string|null $envato_username
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereEnvatoUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereSupportedUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PurchaseCode query()
 */
class PurchaseCode extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['id' => 'integer', 'user_id' => 'integer'];

    /**
     * Many to one relationship with user model.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getSupportedUntilAttribute($value)
    {
        return $value ? $this->serializeDate(Carbon::parse($value)) : null;
    }
}
