<?php namespace App\Policies;

use App\User;
use Common\Core\Policies\BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy extends BasePolicy
{
    public function index(User $user)
    {
        return $user->hasPermission('reports.view');
    }
}
