<?php namespace App\Services\Mail;

use App\Reply;
use Exception;
use App\Ticket;

class TicketReferenceHash
{
    /**
     * Make reference hash string embed for email.
     *
     * @param Reply $reply
     *
     * @return string
     */
    public function makeEmbedForEmail(Reply $reply)
    {
        return "|reference=$reply->uuid|";
    }

    /**
     * Create message id for email with ticket and reply reference embedded.
     *
     * @param Reply $reply
     *
     * @return string
     */
    public function makeMessageIdForEmail(Reply $reply)
    {
        $host = parse_url(config('app.url'))['host'];

        return "{$reply->uuid}@$host";
    }

    /**
     * Extract ticket reference from specified string;
     *
     * @param string $string
     * @return string|null
     */
    public function extractFromString($string)
    {
        preg_match('/\|reference=(.+?)\|/', $string, $matches);

        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Extract reply UUID from email Message-ID header.
     *
     * @param $string
     * @return string
     */
    public function extractFromMessageId($string)
    {
        $uuid = explode('@', $string)[0];

        return str_replace(['<', '>'], '', $uuid);
    }
}
