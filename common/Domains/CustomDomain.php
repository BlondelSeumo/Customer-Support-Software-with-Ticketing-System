<?php

namespace Common\Domains;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Common\Domains\CustomDomain
 * @property string $host // host with protocol already prefixed
 * @property int resource_id
 * @method Builder forUser(int $userId)
 * @mixin Eloquent
 */
class CustomDomain extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'global' => 'boolean',
        'resource_id' => 'int',
    ];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo
     */
    public function resource()
    {
        return $this->morphTo();
    }

    /**
     * Limit query to only custom domains specified user has acess to.
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeForUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId)->orWhere('global', true);
    }
    
    public function getHostAttribute(string $value): string
    {
        return parse_url($value, PHP_URL_SCHEME) === null ? "https://$value" : $value;
    }

    public function setHostAttribute(string $value)
    {
        $this->attributes['host'] = trim($value, '/');
    }
}
