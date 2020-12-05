<?php namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class UserDetailsController extends BaseController
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
     * @param User $user
     * @param Request $request
     */
    public function __construct(User $user, Request $request)
    {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * Update specified user's profile.
     *
     * @param int $userId
     * @return User
     */
    public function update($userId)
    {
        $this->authorize('update', User::class);

        $this->validate($this->request, [
            'details' => 'string|nullable',
            'notes'   => 'string|nullable'
        ]);

        $user = $this->user->with('details')->findOrFail($userId);

        if ( ! $user->details) {
            $user->setRelation('details', $user->details()->create([]));
        }

        $user->details->fill($this->request->all())->save();

        return $user;
    }
}
