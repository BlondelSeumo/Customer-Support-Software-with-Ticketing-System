<?php namespace App\Services\Mail\Transformers;

use Exception;
use GuzzleHttp\Client;
use Arr;

class MailgunMailTransformer implements MailTransformer
{
    /**
     * Http client instance.
     *
     * @var Client
     */
    private $http;

    /**
     * Raw mailgun email data.
     *
     * @var array
     */
    private $emailData;

    /**
     * MailgunParser constructor.
     *
     * @param Client $http
     */
    public function __construct(Client $http)
    {
        $this->http = $http;
    }

    /**
     * Transform mailgun email data into format usable by the app.
     *
     * @param array $emailData
     * @return array
     */
    public function transform($emailData)
    {
        $this->emailData = $emailData;
        $attachments = $this->transformAttachments();

        return [
            'headers' => $this->transformHeaders(),
            'body' => [
                'plain'         => Arr::get($emailData, 'body-plain'),
                'stripped-html' => Arr::get($emailData, 'stripped-html'),
                'html'          => Arr::get($emailData, 'body-html'),
            ],
            'attachments' => $attachments,
        ];
    }

    /**
     * Transform mailgun headers into key => value array.
     *
     * @return array
     */
    public function transformHeaders()
    {
        $headers = Arr::get($this->emailData, 'message-headers', []);

        if ( ! is_array($headers)) $headers = json_decode($headers, true);

        return collect($headers)->mapWithKeys(function($value) {
            return [$value[0] => $this->convertHeaderValueToString($value[1])];
        })->toArray();
    }

    /**
     * Convert mailgun header value into string if it's an array.
     *
     * @param mixed $value
     * @return string
     */
    private function convertHeaderValueToString($value)
    {
        if ( ! is_array($value)) return $value;

        try {
            $value = Arr::flatten($value);
            $value = $value[0] . '; '. $value[1];
        } catch (Exception $e) {
            $value = 'Error parsing header';
        }

        return $value;
    }

    /**
     * Transform mailgun attachments into array.
     *
     * @return array
     */
    private function transformAttachments()
    {
        $attachments = Arr::get($this->emailData, 'attachments', []);

        if (is_string($attachments)) {
            $attachments = json_decode($attachments, true);
        }

        return array_map(function($attachment) {
            $contents = $this->getRemoteAttachmentContents($attachment['url']);
            return [
                'original_name' => $attachment['name'],
                'mime_type'     => $attachment['content-type'],
                'size'          => $attachment['size'],
                'contents'      => $contents,
                'extension'     => explode('/', $attachment['content-type'])[1],
                'cid'           => $this->getAttachmentCid($attachment),
            ];
        }, $attachments);
    }

    /**
     * Find attachment CID if it's an inline attachment.
     *
     * @param array $attachment
     * @return string
     */
    private function getAttachmentCid($attachment)
    {
        $cidMap = Arr::get($this->emailData, 'content-id-map', []);

        if (is_string($cidMap)) {
            $cidMap = json_decode($cidMap, true);
        }

        foreach ($cidMap as $cid => $cidAttachment) {
            $url = is_array($cidAttachment) ? $cidAttachment['url'] : $cidAttachment;

            if ($url === $attachment['url']) {
                return str_replace(['<', '>'], '', $cid);
            }
        }

        return null;
    }

    /**
     * Download specified attachment contents via mailgun API.
     *
     * @param string $url
     * @return string
     */
    private function getRemoteAttachmentContents($url)
    {
        $params = ['auth' => ['api', config('services.mailgun.secret')]];
        $response = $this->http->request('GET', $url, $params);

        return $response ? $response->getBody()->getContents() : null;
    }
}
