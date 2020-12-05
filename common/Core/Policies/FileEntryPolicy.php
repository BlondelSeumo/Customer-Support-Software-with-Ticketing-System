<?php

namespace Common\Core\Policies;

use App\User;
use Arr;
use Common\Files\FileEntry;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Request;

class FileEntryPolicy
{
    use HandlesAuthorization;

    public function index(User $user, array $entryIds = null, int $userId = null): bool
    {
        if ($entryIds) {
            return $this->userCan($user, 'files.view', $entryIds);
        } else {
            return $user->hasPermission('files.view') || $userId === $user->id;
        }
    }

    public function show(User $user, FileEntry $entry): bool
    {
        // allow access via preview token
        if ($entry->preview_token && $entry->preview_token === Request::get('preview_token')) {
            return true;
        }

        return $this->userCan($user, 'files.view', [$entry->id]);
    }

    public function store(User $user, int $parentId = null): bool
    {

        //check if user can modify parent entry (if specified)
        if ($parentId) {
            return $this->userCan($user, 'files.update', [$parentId]);
        }

        return $user->hasPermission('files.create');
    }

    public function update(User $user, array $entryIds)
    {
        return $this->userCan($user, 'files.update', $entryIds);
    }

    public function destroy(User $currentUser, array $entryIds)
    {
        return $this->userCan($currentUser, 'files.delete', $entryIds);
    }

    /**
     * @param User $currentUser
     * @param string $permission
     * @param array|Collection $entries
     * @return bool
     */
    protected function userCan(User $currentUser, string $permission, $entries)
    {
        if ($currentUser->hasPermission($permission)) {
            return true;
        }

        $entries = $this->findEntries($entries);

        // extending class might use "findEntries" method so we load users here
        $entries->load(['users' => function (MorphToMany $builder) use($currentUser) {
            $builder->where('users.id', $currentUser->id);
        }]);

        return $entries->every(function(FileEntry $entry) use($permission) {
            $user = $entry->users->first();
            // user owns entry or was granted specified permission by file owner
            return $user && ($user->owns_entry || Arr::get($user->entry_permissions, $this->sharedFilePermission($permission)));
        });
    }

    /**
     * @param array|Collection $entries
     * @return Collection
     */
    protected function findEntries($entries)
    {
        if (is_array($entries)) {
            return app(FileEntry::class)
                ->whereIn('id', $entries)
                ->get();
        } else {
            return $entries;
        }
    }

    protected function sharedFilePermission($fullPermission): string
    {
        switch ($fullPermission) {
            case 'files.view':
                return 'view';
            case 'files.update':
                return 'edit';
            case 'files.delete';
                return 'delete';
        }
    }
}
