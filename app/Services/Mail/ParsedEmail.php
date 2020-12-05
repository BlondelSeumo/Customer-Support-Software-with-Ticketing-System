<?php namespace App\Services\Mail;

use App;
use App\Services\Mail\Transformers\MailTransformer;
use Common\Settings\Settings;
use EmailReplyParser\Parser\EmailParser;
use Illuminate\Contracts\Support\Jsonable;
use Arr;
use Illuminate\Support\Collection;
use Str;
use InvalidArgumentException;
use ZBateson\MailMimeParser\MailMimeParser;

class ParsedEmail implements Jsonable
{
    /**
     * Raw email data.
     *
     * @var array
     */
    private $email;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var EmailBodyParser
     */
    private $emailBodyParser;

    /**
     * ParsedEmail constructor.
     *
     * @param Settings $settings
     * @param EmailBodyParser $emailBodyParser
     */
    public function __construct(Settings $settings, EmailBodyParser $emailBodyParser)
    {
        $this->settings = $settings;
        $this->emailBodyParser = $emailBodyParser;
    }

    /**
     * Set email data that should be parsed.
     *
     * @param mixed $data
     * @param string|null $transformer
     * @return static
     */
    public function setEmailData($data, $transformer = null)
    {
        $this->email = $this->getMailTransformer($transformer)->transform($data);
        return $this;
    }

    /**
     * Return normalized email body.
     *
     * This will strip quoted replies from email and
     * remove any not allowed html tags.
     *
     * @param array $cidMap
     * @return string
     */
    public function getNormalizedBody($cidMap = [])
    {
        //remove quoted text from email, if not already removed
        if ( ! $this->hasBody('stripped-html')) {
            $body = $this->getBody('html') ?: $this->getBody('plain');
            $this->email['body']['stripped-html'] = (new EmailParser())->parse($body)->getVisibleText();
        }

        $body = $this->email['body']['stripped-html'];

        //replace CIDs in img src with actual image urls
        foreach($cidMap as $cid => $url) {
            $body = str_replace("cid:$cid", $url, $body);
        }

        $parsedBody = $this->emailBodyParser->parse($body);
        if ( ! $parsedBody) {
            $parsedBody = $this->emailBodyParser->parse($this->getBody('plain'));
        }

        return $parsedBody;
    }


    /**
     * Get email subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->getHeader('Subject') ?: '(no subject)';
    }

    /**
     * Get email address of sender.
     *
     * @throws InvalidArgumentException
     * @return string
     */
    public function getSenderEmail()
    {
        $header = $this->getHeader('Reply-To') ?: $this->getHeader('From');

        $email = (new MailMimeParser())->parse("From: $header")->getHeader('From')->getEmail();

        if ($email) {
            return $email;
        }

        throw new InvalidArgumentException("Could not extract email address from [$header]");
    }

    /**
     * Get email header by specified name.
     *
     * @param string $name
     * @return string
     */
    public function getHeader($name)
    {
        return Arr::get($this->email, "headers.$name");
    }

    /**
     * Check if email has specified header.
     *
     * @param string $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return Arr::has($this->email, "headers.$name");
    }

    /**
     * Get all email headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return Arr::get($this->email, 'headers', []);
    }

    /**
     * Get email body by type (plain, html, stripped-html)
     *
     * @param string $type
     * @return string
     */
    public function getBody($type)
    {
        return Arr::get($this->email, "body.$type");
    }

    /**
     * Check if email has body of specified type.
     *
     * @param string $type
     * @return bool
     */
    public function hasBody($type)
    {
        return Arr::get($this->email, "body.$type") !== null;
    }

    /**
     * Get email attachments.
     *
     * @param string $type
     * @return Collection
     */
    public function getAttachments($type)
    {
        $attachments = Arr::get($this->email, 'attachments', []);

        //if attachment has a CID then it's inline, otherwise it's 'regular'
        return collect($attachments)->filter(function($attachment) use($type) {
            $cidEmbedded = $attachment['cid'] && \Str::contains($this->getBody('html'), $attachment['cid']);

            //if email body does not have attachment CID embedded, treat attachment as 'regular'
            if ($type === 'inline') {
                return $cidEmbedded;
            } else {
                return ! $cidEmbedded;
            }
        });
    }

    /**
     * Convert the parsed email to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode([
            'headers' => $this->getHeaders(),
            'body' => [
                'plain' => $this->getBody('plain'),
                'html' => $this->getBody('html'),
            ]
        ], $options);
    }

    /**
     * Get an instance of mail.handler.
     *
     * @param string|null $transformer
     * @return MailTransformer
     */
    private function getMailTransformer($transformer = null)
    {
        if ($transformer) {
            $name = $transformer;
        } else if ($default = App::make(Settings::class)->get('mail.handler')) {
            $name = $default;
        } else {
            $name = 'null';
        }

        $class = 'App\Services\Mail\Transformers\\'.Str::studly($name).'MailTransformer';

        if (class_exists($class)) {
            return App::make($class);
        }

        throw new InvalidArgumentException("Mail Transformer [$name] not supported.");
    }

}
