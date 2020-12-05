<?php namespace Common\Auth\Controllers;

use Carbon\Carbon;
use Common\Auth\UserRepository;
use Common\Core\BaseController;
use Common\Core\Bootstrap\BootstrapData;
use Common\Settings\Settings;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;

class RegisterController extends BaseController
{
    use RegistersUsers;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var BootstrapData
     */
    private $bootstrapData;

    /**
     * @param Settings $settings
     * @param UserRepository $repository
     * @param BootstrapData $bootstrapData
     */
    public function __construct(Settings $settings, UserRepository $repository, BootstrapData $bootstrapData)
    {
        $this->settings = $settings;
        $this->repository = $repository;
        $this->bootstrapData = $bootstrapData;

        $this->middleware('guest');

        // abort if registration should be disabled
        if ($this->settings->get('disable.registration')) abort(404);
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:5', 'confirmed'],
        ]);

        $params = $request->all();
        if ( ! $this->settings->get('require_email_confirmation')) {
            $params['email_verified_at'] = Carbon::now();
        }

        event(new Registered($user = $this->repository->create($params)));

        if ($user->hasVerifiedEmail()) {
            $this->guard()->login($user);
        }

        $response = ['status' => $user->hasVerifiedEmail() ? 'success' : 'needs_email_verification'];

        if ($user->hasVerifiedEmail()) {
            $response['bootstrapData'] = $this->bootstrapData->init()->getEncoded();
        } else {
            $response['message'] = 'We have sent you an email with instructions on how to activate your account.';
        }

        return $this->success($response);
    }
}
