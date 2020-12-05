<?php namespace App\Services\Mail\Verifiers;

use Common\Settings\Settings;
use Arr;

class NullWebhookVerifier
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * NullWebhookVerifier constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Verify if webhook request is coming from mailgun.
     *
     * @param array $data
     * @return bool
     */
    public function verify($data)
    {
        $key = $this->settings->get('mail.webhook_secret_key');

        if ( ! $key) return false;

        return Arr::get($data, 'webhook_secret_key') === $key;
    }
}
