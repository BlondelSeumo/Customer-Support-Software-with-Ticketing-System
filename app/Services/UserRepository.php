<?php namespace App\Services;

use App\Services\Envato\EnvatoApiClient;
use App\User;
use Common\Auth\Events\UserCreated;
use Common\Auth\Roles\Role;
use Common\Auth\UserRepository as CommonUserRepository;
use Common\Settings\Settings;
use Illuminate\Http\JsonResponse;
use Arr;
use Illuminate\Validation\ValidationException;
use Validator;

class UserRepository extends CommonUserRepository {

    /**
     * @var EnvatoApiClient
     */
    private $envato;

    /**
     * @param User $user
     * @param Role $role
     * @param Settings $settings
     * @param EnvatoApiClient $envato
     */
    public function __construct(
        User $user,
        Role $role,
        Settings $settings,
        EnvatoApiClient $envato
    )
    {
        parent::__construct($user, $role, $settings);
        $this->envato = $envato;
    }

    /**
     * @param array $params
     * @return User
     */
    public function create($params)
    {
        /** @var User $user */
        $user = $this->user->forceCreate($this->formatParams($params));

        try {
            // TODO: move this into bedesk specific event listener or elsewhere
            // TODO: after user repository is refactored.
            if (isset($params['purchase_code'])) {
                $purchase = $this->envato->getPurchaseByCode($params['purchase_code']);

                // TODO: should probably clean this up
                if ( ! $purchase) {
                    $validator = Validator::make([], []); // Empty data and rules fields
                    $validator->errors()->add('purchase_code', 'This purchase code is not valid.');
                    throw new ValidationException($validator, new JsonResponse(['messages' => $validator->errors()->getMessages()], 422));
                }

                $user->updatePurchases([$purchase], Arr::get($purchase, 'buyer'));
            }

            if ( ! isset($params['roles']) || ! $this->attachRoles($user, $params['roles'])) {
                $this->assignDefaultRole($user);
            }
        } catch (\Exception $e) {
            //delete user if there were any errors creating/assigning
            //purchase codes or roles, so there are no artifacts left
            $user->delete();
            throw($e);
        }

        event(new UserCreated($user));

        return $user;
    }
}
