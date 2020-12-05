<?php

namespace Common\Workspaces;

use App\User;
use Arr;
use Auth;
use Str;

class ActiveWorkspace
{
    /**
     * @var Workspace|null;
     */
    private $cachedWorkspace;
    private $resourceCountCache = [];
    private $memberCache = [];
    public $id;

    public function __construct()
    {
        $this->id = (int) Arr::get($_COOKIE, $this->cookieName()) ?: null;
    }

    public function workspace(): ?Workspace
    {
        if (is_null($this->cachedWorkspace)) {
            $workspaceId = $this->id;
            $this->cachedWorkspace = Workspace::find($workspaceId) ?? 0;
            if ( ! $this->cachedWorkspace) {
                cookie()->queue($this->cookieName(), null, -2628000, null, null, null, false);
            }
        }

        return $this->cachedWorkspace ? $this->cachedWorkspace :  null;
    }

    public function personal()
    {
        return !$this->id;
    }

    public function owner(): User
    {
        return $this->workspace()->owner_id === Auth::id() ? Auth::user() : $this->workspace()->owner;
    }

    public function currentUserIsOwner(): bool {
        return $this->personal() || $this->workspace()->owner_id === Auth::id();
    }

    /**
     * @return User|Workspace
     */
    public function modelForLinkClickQuery()
    {
        return $this->personal() ? Auth::user() : $this->workspace();
    }

    public function member(int $userId): ?WorkspaceMember
    {
        if ( ! isset($this->memberCache[$userId])) {
            $this->memberCache[$userId] = app(WorkspaceMember::class)->where([
                'user_id' => $userId,
                'workspace_id' => $this->workspace()->id,
            ])->first();
        }
        return $this->memberCache[$userId];
    }

    public function getRestrictionValue(string $permissionName, string $restriction): ?int
    {
        return $this->personal() ?
            (Auth::check() ? Auth::user()->getRestrictionValue($permissionName, $restriction) : null) :
            $this->owner()->getRestrictionValue($permissionName, $restriction);
    }

    public function getResourceCount(string $resource): ?int
    {
        $relationName = Str::camel(Str::plural(class_basename($resource)));
        if ( ! isset($this->resourceCountCache[$relationName])) {
            $this->resourceCountCache[$relationName] = $this->personal() ?
                Auth::user()->$relationName()->count() :
                $this->workspace()->$relationName()->count();
        }
        return $this->resourceCountCache[$relationName];
    }

    private function cookieName()
    {
        $userId = Auth::id();
        return "{$userId}_activeWorkspace";
    }
}
