<?php

namespace Common\Domains;

use App\User;
use Common\Core\Policies\BasePolicy;

class CustomDomainPolicy extends BasePolicy
{
    public function index(User $user, $userId = null)
    {
        return $user->hasPermission('custom_domains.view') || $user->id === (int) $userId;
    }

    public function show(User $user, CustomDomain $customDomain)
    {
        return $user->hasPermission('custom_domains.view') || $customDomain->user_id === $user->id;
    }

    public function store(User $user)
    {
        return $this->storeWithCountRestriction($user, CustomDomain::class);
    }

    public function update(User $user)
    {
        return $user->hasPermission('custom_domains.update');
    }

    /**
     * @param User $user
     * @param array $domainIds
     * @return bool
     */
    public function destroy(User $user, $domainIds)
    {
        if ($user->hasPermission('custom_domains.delete')) {
            return true;
        } else {
            $dbCount = app(CustomDomain::class)
                ->whereIn('id', $domainIds)
                ->where('user_id', $user->id)
                ->count();
            return $dbCount === count($domainIds);
        }
    }
}
