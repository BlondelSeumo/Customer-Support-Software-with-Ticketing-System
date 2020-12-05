<?php namespace App\Services\Mail;

class EmailBodyParser
{
    /**
     * Parse specified email body for ticket reply.
     *
     * @param string $emailBody
     * @return string
     */
    public function parse($emailBody)
    {
        //purify email body
        $emailBody = $this->getPurifier()->purify($emailBody);

        //replace all newlines with "br" tag
        $emailBody = str_replace(["\r\n", "\r", "\n"], "<br>", $emailBody);

        //remove all whitespace/newlines from start and end of email body
        $emailBody = trim($emailBody);
        $emailBody = preg_replace('/(?:<br\s*\/?>\s*)+$/', '', $emailBody);
        $emailBody = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $emailBody);

        //replace 3 or more br tags with 2 to avoid excessive white space
        $emailBody = preg_replace('/(?:<br\s*\/?>\s*){3,}/', '<br><br>', $emailBody);

        return $emailBody;
    }

    /**
     * Configure and return HtmlPurifier instance.
     *
     * @return \HTMLPurifier
     */
    private function getPurifier()
    {
        $config = \HTMLPurifier_Config::createDefault();

        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('Cache.SerializerPath', storage_path('app/purifier'));
        $config->set('HTML.Allowed', 'b,strong,i,u,a[href],ul,ol,li,br,img[src|width|height],font[color]');
        $config->set('HTML.TargetBlank', true);
        $config->set('AutoFormat.Linkify', true);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);

        return new \HTMLPurifier($config);
    }
}
