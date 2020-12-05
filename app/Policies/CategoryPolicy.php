<?php namespace App\Policies;

use App\User;
use Common\Core\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy extends BasePolicy
{
    public function show(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'categories.view');
    }

    public function index(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'categories.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('categories.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('categories.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('categories.delete');
    }
}
