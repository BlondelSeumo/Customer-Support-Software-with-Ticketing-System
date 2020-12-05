<?php namespace App\Services\Mail\Transformers;

use ZBateson\MailMimeParser\MailMimeParser;

class MimeMailTransformer implements MailTransformer
{
    /**
     * Transform raw email mime data into format usable by the app.
     *
     * @param string $emailData
     * @return array
     */
    public function transform($emailData)
    {
        $mailParser = new MailMimeParser();
        $message = $mailParser->parse($emailData);

        $parsed = [
            'headers' => [],
            'body' => [
                'html' => '',
                'plain' => '',
            ],
            'attachments' => [],
        ];

        foreach ($message->getAllHeaders() as $header) {
           $parsed['headers'][$header->getName()] = $header->getValue();
        }

        $parsed['body']['html'] = $message->getHtmlContent();
        $parsed['body']['plain'] = $message->getTextContent();

        foreach ($message->getAllAttachmentParts() as $attachment) {
            $content =  $attachment->getContent();
            $parsed['attachments'][] = [
                'contents' => $content,
                'original_name' => $attachment->getFilename(),
                'mime_type' => $attachment->getContentType(),
                'size' => strlen($content),
                'extension' => pathinfo($attachment->getFilename(), PATHINFO_EXTENSION),
                'cid' => $attachment->getContentId(),
            ];
        }

        return $parsed;
    }

}
