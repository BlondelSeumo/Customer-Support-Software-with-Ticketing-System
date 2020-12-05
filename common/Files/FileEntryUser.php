<?php

namespace Common\Files;

use App\User;
use Common\Auth\BaseUser;

/**
 * @property boolean $owns_entry
 * @property array $entry_permissions
 */
class FileEntryUser extends BaseUser
{
    protected $table = 'users';

    protected $billingEnabled = false;

    public function getMorphClass()
    {
        return User::class;
    }

    protected $hidden = [
        'password', 'remember_token', 'first_name', 'last_name', 'has_password', 'pivot'
    ];

    protected $appends = ['owns_entry', 'entry_permissions', 'display_name'];

    public function getOwnsEntryAttribute() {
        return $this->pivot->owner;
    }

    public function getEntryPermissionsAttribute() {
        return $this->pivot->permissions;
    }
}
