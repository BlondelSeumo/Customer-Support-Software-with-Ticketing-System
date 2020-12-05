<?php namespace App\Services\Mail\Verifiers;

use App;
use Common\Settings\Settings;
use Str;
use InvalidArgumentException;

class MailWebhookVerifier
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * MailWebhookVerifier constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Check if webhook request is valid.
     *
     * Request is valid if it is properly
     * signed or coming from authorized domain.
     *
     * @param array $requestParams
     * @return bool
     */
    public function isValid($requestParams)
    {
        if (empty($requestParams)) return false;

        return $this->getVerifier()->verify($requestParams);
    }

    /**
     * Get an instance of mail.handler.
     *
     * @throws InvalidArgumentException
     * @return MailgunWebhookVerifier
     */
    private function getVerifier()
    {
        $name = $this->settings->get('mail.handler') ?: 'null';

        $class = 'App\Services\Mail\Verifiers\\'.Str::studly($name).'WebhookVerifier';

        if (class_exists($class)) {
            return App::make($class);
        }

        throw new InvalidArgumentException("Webhook verifier [$name] not supported.");
    }
}
