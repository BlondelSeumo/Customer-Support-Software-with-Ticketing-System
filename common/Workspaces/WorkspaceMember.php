<?php

namespace Common\Workspaces;

use Auth;
use Common\Auth\Permissions\Permission;
use Common\Auth\Roles\Role;
use Common\Auth\Traits\HasAvatarAttribute;
use Common\Auth\Traits\HasDisplayNameAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Workspace workspace
 * @property Permission[]|Collection permissions
 * @property boolean is_owner
 */
class WorkspaceMember extends Model
{
    use HasAvatarAttribute, HasDisplayNameAttribute;

    protected $table = 'workspace_user';
    protected $guarded = ['id'];
    protected $appends = ['display_name', 'model_type'];
    protected $casts = ['is_owner' => 'boolean'];

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'permissionables', 'permissionable_id', 'permission_id', 'role_id')
            ->where('permissionable_type', Role::class)
            ->select(['permissions.id', 'permissions.name', 'permissions.restrictions']);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function scopeCurrentUserAndOwnerOnly(Builder $builder): self
    {
        $builder->where(function(Builder $builder) {
            $builder->where('workspace_user.user_id', Auth::id())
                ->orWhere('workspace_user.is_owner', true);
        });

        return $this;
    }
    
    public function getModelTypeAttribute() {
        return 'member';
    }

    public function hasPermission(string $name): bool
    {
        return $this->is_owner || !is_null($this->getPermission($name));
    }

    public function getPermission(string $name): ?Permission
    {
        return $this->permissions->first(function(Permission $permission) use($name) {
            return $permission->name === $name;
        });
    }

    public function getRoleNameAttribute() {
        return $this->is_owner ? 'Workspace Owner' : $this->attributes['role_name'];
    }
}
