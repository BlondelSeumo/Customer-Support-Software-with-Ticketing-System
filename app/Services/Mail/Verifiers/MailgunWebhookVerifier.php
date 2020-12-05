<?php namespace App\Services\Mail\Verifiers;

use Illuminate\Support\Arr;

class MailgunWebhookVerifier
{
    /**
     * Verify if webhook request is coming from mailgun.
     *
     * @param array $data
     * @return bool
     */
    public function verify($data)
    {
        $apiKey     = config('services.mailgun.secret');
        $token      = Arr::get($data, 'token');
        $timestamp  = Arr::get($data, 'timestamp');
        $signature  = Arr::get($data, 'signature');

        if ( ! $apiKey || ! $token || ! $timestamp || ! $signature) return false;

        //check if the timestamp is fresh
        if (abs(time() - $timestamp) > 15) {
            return false;
        }

        //returns true if signature is valid
        return hash_hmac('sha256', $timestamp.$token, $apiKey) === $signature;
    }
}
