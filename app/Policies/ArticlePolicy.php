<?php namespace App\Policies;

use App\User;
use Common\Core\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlePolicy extends BasePolicy
{
    public function index(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'articles.view');
    }

    public function show(?User $user)
    {
        return $this->userOrGuestHasPermission($user, 'articles.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('articles.create');
    }

    public function update(User $user)
    {
        return $user->hasPermission('articles.update');
    }

    public function destroy(User $user)
    {
        return $user->hasPermission('articles.delete');
    }
}
