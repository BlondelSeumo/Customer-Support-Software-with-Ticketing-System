<?php namespace App\Services\Envato;

use Cache;
use Carbon\Carbon;
use Common\Core\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class EnvatoApiClient
{
    /**
     * Guzzle Http client.
     *
     * @var Client
     */
    private $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Returns details of user's purchase matching specified code.
     *
     * @param string $code
     * @return array|null
     */
    public function getPurchaseByCode($code)
    {
        if ( ! $code) return null;

       return Cache::remember("purchase.$code", Carbon::now()->addMinutes(10), function() use($code) {
            try {
                $response = $this->call('author/sale', ['code' => $code]);
                if ( ! isset($response['item'])) {
                    return null;
                }
                $response['code'] = $code;
                return $response;
            } catch(TransferException $e) {
                return null;
            }
       });
    }

    /**
     * Check if specified purchase code is valid.
     *
     * @param $code
     * @return boolean
     */
    public function purchaseCodeIsValid($code)
    {
        return $this->getPurchaseByCode($code) ? true : false;
    }

    /**
     * Get envato items of user.
     *
     * @return array
     */
    public function getItems() {
        $response = $this->call('market/private/user/username.json', [], 'v1');
        $response = $this->call('discovery/search/search/item', ['username' => $response['username']], 'v1');

        return array_map(function($item) {
            return $item['name'];
        }, $response['matches']);
    }

    /**
     * Call given envato API uri.
     *
     * @param string  $uri
     * @param array   $params
     * @param string  $version
     * @return array
     */
    public function call($uri, $params = [], $version = 'v3')
    {
        if ($version === 'v3') {
            $base = 'https://api.envato.com/v3/market/';
        } else {
            $base = 'https://api.envato.com/v1/';
        }

        $response = $this->http->get("{$base}{$uri}", [
            'headers' => ['Authorization' => 'Bearer '.config('services.envato.personal_token')],
            'query'   => $params,
        ]);

        return $response ?: [];
    }
}
