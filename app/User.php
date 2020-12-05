<?php namespace App;

use Carbon\Carbon;
use Common\Auth\BaseUser;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Arr;
use Laravel\Scout\Searchable;

/**
 * App\User
 *
 * @property-read Collection|Ticket[] $tickets
 * @property-read Collection|Reply[] $replies
 * @property-read Collection|CannedReply[] $cannedReplies
 * @property-read Collection|PurchaseCode[] $envato_purchase_codes
 * @property-read Collection|PurchaseCode[] $purchase_codes
 * @property-read Collection|Email[] $secondary_emails
 * @property-read UserDetails $details
 *  @mixin Eloquent
 */
class User extends BaseUser
{
    use Notifiable, Searchable;

    protected $billingEnabled = false;

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'envato_username' => $this->purchase_codes->pluck('envato_username')
        ];
    }

    /**
     * Tickets created by this user.
     *
     * @return HasMany
     */
    public function tickets()
    {
        return $this->hasMany('App\Ticket')->orderBy('created_at', 'desc');
    }

    /**
     * User profile.
     *
     * @return HasOne
     */
    public function details()
    {
        return $this->hasOne(UserDetails::class);
    }

    /**
     * Secondary email address belonging to user.
     *
     * @return HasMany
     */
    public function secondary_emails()
    {
        return $this->hasMany(Email::class);
    }

    /**
     * Envato purchase codes attached to this user.
     *
     * @return HasMany
     */
    public function purchase_codes()
    {
        return $this->hasMany(PurchaseCode::class)->orderBy('created_at', 'desc');
    }

    /**
     * Replies submitted by this user.
     *
     * @return HasMany
     */
    public function replies()
    {
        return $this->hasMany('App\Reply');
    }

    /**
     * Canned replies created by this user.
     *
     * @return HasMany
     */
    public function cannedReplies()
    {
        return $this->hasMany('App\CannedReply');
    }

    /**
     * Tags that are attached to the user.
     *
     * @return MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany('App\Tag', 'taggable');
    }

    /**
     * Search users using basic mysql LIKE query.
     *
     * @param string $query
     * @return Builder
     */
    public function basicSearch($query)
    {
        return $this->where('email', 'LIKE', "$query%")
            ->orWhere('first_name', 'LIKE', "$query%")
            ->orWhere('last_name', 'LIKE', "$query%");
    }

    /**
     * Create new envato purchase code from
     * specified details and attach it to user.
     *
     * @param array $purchases
     * @param string $envatoUsername
     * @return PurchaseCode[]
     */
    public function updatePurchases($purchases, $envatoUsername = null) {
        return array_map(function($purchaseDetails) use($envatoUsername) {
            $supportedUntil = Arr::get($purchaseDetails, 'supported_until');
            return $this->purchase_codes()->updateOrCreate(['code' => $purchaseDetails['code']], [
                'item_name' => $purchaseDetails['item']['name'],
                'item_id'   => $purchaseDetails['item']['id'],
                'code'      => $purchaseDetails['code'],
                'supported_until' => $supportedUntil ? Carbon::parse($supportedUntil) : null,
                'url'       => Arr::get($purchaseDetails, 'item.url'),
                'image'     => Arr::get($purchaseDetails, 'item.previews.icon_preview.icon_url'),
                'envato_username' => $envatoUsername ?: Arr::get($purchaseDetails, 'buyer'),
            ]);
        }, $purchases);
    }

    /**
     * Check if user is a super admin.
     *
     * @return boolean
     */
    public function isSuperAdmin()
    {
        return $this->hasPermission('superAdmin') || $this->hasPermission('admin');
    }

    /**
     * Check if user is an agent.
     *
     * @return bool
     */
    public function isAgent()
    {
        return $this->isSuperAdmin() || $this->belongsToRole('agents') || $this->hasPermission('tickets.update');
    }

    /**
     * Check if user belongs to specified group.
     *
     * @param string $name
     * @return bool
     */
    public function belongsToRole($name)
    {
        return $this->roles->contains('name', $name);
    }
}
