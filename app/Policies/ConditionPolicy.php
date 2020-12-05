<?php namespace App\Policies;

use App\User;
use Common\Core\Policies\BasePolicy;
use Faker\Provider\Base;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConditionPolicy extends BasePolicy
{
    public function index(User $user)
    {
        return $user->hasPermission('conditions.view');
    }
}
