<?php

namespace Common\Notifications;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string notif_id
 * @property array channels
 * @mixin Eloquent
 */
class NotificationSubscription extends Model
{
    protected $guarded = ['id'];
    protected $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;

    protected $casts = [
        'user_id' => 'integer',
        'channels' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Uuid::uuid4());
        });
    }
}
