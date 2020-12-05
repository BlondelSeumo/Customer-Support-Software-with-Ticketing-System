<?php namespace App\Services\Mail\Transformers;

interface MailTransformer
{
    /**
     * Transform email data into format usable by the app.
     *
     * @param mixed $emailData
     * @return array
     */
    public function transform($emailData);
}
