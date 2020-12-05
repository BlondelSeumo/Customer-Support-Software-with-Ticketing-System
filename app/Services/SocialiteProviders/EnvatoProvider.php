<?php namespace App\Services\SocialiteProviders;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class EnvatoProvider extends AbstractProvider implements ProviderInterface {

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected $scopes = ['user:account', 'user:username', 'user:email'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://api.envato.com/authorization', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://api.envato.com/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.envato.com/v1/market/private/user/account.json', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);
        $response = json_decode($response->getBody()->getContents(), true)['account'];

        $response['email'] = $this->getEmailByToken($token);
        $response['username'] = $this->getUsernameByToken($token);
        $response['purchases'] = $this->getPurchasesByToken($token);

        return $response;
    }

    /**
     *  Get the account email of the current user.
     *
     * @param string $token
     *
     * @return string
     */
    protected function getEmailByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.envato.com/v1/market/private/user/email.json', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true)['email'];
    }

    /**
     *  Get the account username of the current user.
     *
     * @param string $token
     *
     * @return string
     */
    protected function getUsernameByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.envato.com/v1/market/private/user/username.json', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true)['username'];
    }

    /**
     * Get user purchases (only the purchases from current envato client ID owner).
     *
     * @param string $token
     *
     * @return string
     */
    protected function getPurchasesByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.envato.com/v3/market/buyer/purchases', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);
        return json_decode($response->getBody()->getContents(), true)['purchases'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id'        => null,
            'nickname'  => $user['username'],
            'name'      => $user['firstname'].' '.$user['surname'],
            'email'     => $user['email'],
            'avatar'    => $this->transformImageUrl($user['image']),
            'purchases' => $user['purchases'],
        ]);
    }

    /**
     * Replace :// with https:// as the former will cause issues.
     *
     * @param string $url
     * @return string
     */
    private function transformImageUrl($url)
    {
        return preg_replace('/^\/\//', 'https://', $url);
    }
}
