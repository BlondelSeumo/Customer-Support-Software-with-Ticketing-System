<?php

namespace Common\Auth\Actions;

use App\User;
use Common\Database\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Arr;

class PaginateUsers
{
    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function execute($params)
    {
        $paginator = (new Paginator($this->user, $params))
            ->with(['roles', 'permissions']);
        $paginator->filterColumns = ['email_verified_at', 'created_at', 'subscribed' => function(Builder $builder) {
            $builder->whereHas('subscriptions');
        }];

        $paginator->searchCallback = function(Builder $builder, $query) {
            $builder->where('email', 'LIKE', "%$query%")
                ->orWhere('first_name', 'LIKE', "$query%");
        };

        if ($roleId = Arr::get($params, 'role_id')) {
            $paginator->setDefaultOrderColumns('created_at', 'desc');
            $paginator->query()->join('user_role', 'users.id', '=', 'user_role.user_id')
                ->where('user_role.role_id', $roleId)
                ->select(['users.id', 'avatar', 'email', 'first_name', 'last_name', 'user_role.created_at as created_at']);
        }

        if ($roleName = Arr::get($params, 'role_name')) {
            $paginator->query()->whereHas('roles', function(Builder $q) use($roleName) {
                $q->where('roles.name', $roleName);
            });
        }

        if ($permission = Arr::get($params, 'permission')) {
            $paginator->query()
                ->whereHas('permissions', function(Builder $query) use($permission) {
                    $query->where('name', $permission)->orWhere('name', 'admin');
                })
                ->orWhereHas('roles', function(Builder $query) use($permission) {
                    $query->whereHas('permissions', function(Builder $query) use($permission) {
                        $query->where('name', $permission)->orWhere('name', 'admin');
                    });
                });
        }

        return $paginator->paginate();
    }
}
