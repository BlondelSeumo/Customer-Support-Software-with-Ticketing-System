<?php

namespace Common\Core\Policies;

use App\User;
use Common\Auth\BaseUser;
use Common\Auth\Roles\Role;
use Common\Core\Exceptions\AccessResponseWithAction;
use Common\Settings\Settings;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Http\Request;
use Str;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Settings
     */
    protected $settings;

    public function __construct(Request $request, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    protected function userOrGuestHasPermission(?User $user, string $permission)
    {
        if ($user) {
            return $user->hasPermission($permission);
        } else {
            if ($guestRole = Role::where('guests', true)->first()) {
                return $guestRole->hasPermission($permission);
            }
        }
        return false;
    }

    protected function denyWithAction($message, array $action = null)
    {
        /** @var AccessResponseWithAction $response */
        $response = AccessResponseWithAction::deny($message, $action);
        $response->action = $action;
        return $response;
    }

    /**
     * @param BaseUser $user
     * @param string $namespace
     * @return bool|AccessResponseWithAction
     */
    protected function storeWithCountRestriction($user, $namespace) {
        [$relationName, $permission, $singularName, $pluralName] = $this->parseNamespace($namespace);

        // user can't create resource at all
        if ( ! $user->hasPermission($permission)) {
            return false;
        }

        // user is admin, can ignore count restriction
        if ($user->hasPermission('admin')) {
            return true;
        }

        // user does not have any restriction on maximum resource count
        $maxCount = $user->getRestrictionValue($permission, 'count');
        if ( ! $maxCount) {
            return true;
        }

        // check if user did not go over their max quota
        if ($user->$relationName->count() >= $maxCount) {
            $message = __('policies.quota_exceeded', ['resources' => $pluralName, 'resource' => $singularName]);
            return $this->denyWithAction($message, $this->upgradeAction());
        }

        return true;
    }

    protected function parseNamespace(string $namespace, string $ability = 'create'): array
    {
        // 'App\SomeModel' => 'Some_Model'
        $resourceName = Str::snake(class_basename($namespace));

        // 'Some_Model' => 'some_models'
        $relationName = strtolower(Str::plural($resourceName));

        // 'Some_Model' => 'Some Model'
        $singularName = str_replace('_', ' ', $resourceName);

        // 'Some Model' => 'Some Models'
        $pluralName = Str::plural($singularName);

        // parent might need to override permission name. custom_domains instead of links_domains for example.
        $permissionName = $this->permissionName ?? $relationName;

        return [$relationName, "$permissionName.$ability", $singularName, $pluralName];
    }

    protected function upgradeAction(): ?array
    {
        if ($this->settings->get('billing.enable')) {
            return ['label' => 'Upgrade', 'action' => '/billing/upgrade'];
        } else {
            return null;
        }
    }
}
