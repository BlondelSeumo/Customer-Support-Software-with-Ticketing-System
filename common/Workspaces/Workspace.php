<?php

namespace Common\Workspaces;

use App\User;
use App\Workspaces\WorkspaceRelationships;
use Auth;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Workspace
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection $members
 * @property User $owner
 * @property int owner_id
 * @mixin Eloquent
 */
class Workspace extends Model
{
    use WorkspaceRelationships;

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'owner_id' => 'integer',
    ];

    public function invites(): HasMany
    {
        return $this->hasMany(WorkspaceInvite::class)
            ->join('roles', 'roles.id', '=', 'workspace_invites.role_id')
            ->select([
                'workspace_invites.id',
                'workspace_invites.workspace_id',
                'roles.name as role_name',
                'workspace_invites.email',
                'workspace_invites.role_id',
                'email', 'avatar'
            ]);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id')
            ->select(['id', 'email', 'first_name', 'last_name', 'avatar']);
    }

    public function members()
    {
        return $this->hasMany(WorkspaceMember::class)
            ->join('roles', 'roles.id', '=', 'workspace_user.role_id', 'left')
            ->join('users', 'users.id', '=', 'workspace_user.user_id')
            ->select([
                'roles.name as role_name',
                'users.email',
                'workspace_user.workspace_id',
                'workspace_user.created_at as joined_at',
                'workspace_user.role_id',
                'workspace_user.is_owner',
                'workspace_user.id as member_id',
                'users.id', 'users.first_name', 'users.last_name', 'users.avatar'
            ]);
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function findMember(User $user): WorkspaceMember
    {
        return $this->members()->where('user_id', $user->id)->first();
    }

    public function scopeForUser(Builder $builder, int $userId): Builder
    {
        return $builder->where('owner_id', $userId)
            ->orWhereHas('members', function(Builder $builder) use($userId) {
                return $builder->where('workspace_user.user_id', $userId);
            });
    }

    public function setCurrentUserAndOwner(): self
    {
        $this->setRelation('owner', $this->members->where('is_owner', true)->first());
        $this->currentUser = $this->members->where('id', Auth::id())->first();
        $this->unsetRelation('members');

        // load workspace permissions for current user in case front-end needs it
        if (app(ActiveWorkspace::class)->id === $this->id && ! $this->currentUser->relationLoaded('permissions')) {
            $this->currentUser->load('permissions');
        }

        return $this;
    }
}
