<?php namespace App\Http\Controllers;

use App\Tag;
use App\User;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class UserTagsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @param User $user
     * @param Tag $tag
     * @param Request $request
     */
    public function __construct(User $user, Tag $tag, Request $request)
    {
        $this->request = $request;
        $this->user = $user;
        $this->tag = $tag;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function sync($userId)
    {
        $this->authorize('update', User::class);

        $this->validate($this->request, [
            'tags'   => 'array',
            'tags.*' => 'string'
        ]);

        $user = $this->user->findOrFail($userId);

        $tagIds = collect($this->request->get('tags'))->map(function($tagName) {
            return $this->tag->firstOrCreate(['name' => $tagName]);
        })->pluck('id');

        return $user->tags()->sync($tagIds);
    }
}
