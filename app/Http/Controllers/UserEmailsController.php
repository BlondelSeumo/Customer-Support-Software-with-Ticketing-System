<?php namespace App\Http\Controllers;

use App\User;
use App\Email;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class UserEmailsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Email
     */
    private $email;

    /**
     * @var User
     */
    private $user;

    /**
     * @param Request $request
     * @param Email $email
     * @param User $user
     */
    public function __construct(Request $request, Email $email, User $user)
    {
        $this->email = $email;
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * Attach specified emails to user.
     *
     * @param int $userId
     * @return User
     */
    public function attach($userId)
    {
        $this->authorize('update', User::class);

        $user = $this->user->findOrFail($userId);

        $this->validate($this->request, [
            'emails'   => 'required|array|min:1',
            'emails.*' => 'required|string|email|unique:emails,address|unique:users,email',
        ]);

        collect($this->request->get('emails'))->each(function($email) use($user) {
            $user->secondary_emails()->create(['address' => $email]);
        });

        return $this->success(['user' => $user]);
    }

    /**
     * Detach specified emails from user.
     *
     * @param int $userId
     * @return User
     */
    public function detach($userId)
    {
        $this->authorize('update', User::class);

        $this->validate($this->request, [
            'emails'   => 'required|array|min:1',
            'emails.*' => 'required|string|email|exists:emails,address',
        ]);

        $user = $this->user->with('secondary_emails')->findOrFail($userId);

        $user->secondary_emails->each(function($email) {
            if (collect($this->request->get('emails'))->contains($email->address)) {
                $email->delete();
            }
        });

        return $this->success(['user' => $user]);
    }
}
