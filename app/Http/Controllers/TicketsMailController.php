<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Mail\FailedMailHandler;
use App\Services\Mail\IncomingMailHandler;
use App\Services\Mail\Verifiers\MailWebhookVerifier;
use Common\Core\BaseController;

class TicketsMailController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var IncomingMailHandler
     */
    private $mailHandler;

    /**
     * @var FailedMailHandler
     */
    private $failedMailHandler;

    /**
     * @var MailWebhookVerifier
     */
    private $mailWebhookVerifier;

    /**
     * @param Request $request
     * @param IncomingMailHandler $mailHandler
     * @param FailedMailHandler $failedMailHandler
     * @param MailWebhookVerifier $mailWebhookVerifier
     */
    public function __construct(
        Request $request,
        IncomingMailHandler $mailHandler,
        FailedMailHandler $failedMailHandler,
        MailWebhookVerifier $mailWebhookVerifier
    )
    {
        $this->request = $request;
        $this->mailHandler = $mailHandler;
        $this->failedMailHandler = $failedMailHandler;
        $this->mailWebhookVerifier = $mailWebhookVerifier;
    }

    /**
     * Webhook for handling incoming ticket emails.
     *
     * @return mixed
     */
    public function handleIncoming()
    {
        if ( ! $this->mailWebhookVerifier->isValid($this->request->all())) {
            return response('', 406);
        }

        $this->mailHandler->parseEmailIntoTicketOrReply($this->request->all());
    }

    /**
     * Webhook for handling failed ticket emails.
     *
     * @return mixed
     */
    public function handleFailed()
    {
        if ( ! $this->mailWebhookVerifier->isValid($this->request->all())) {
            return response('', 406);
        }

        $this->failedMailHandler->createTicketForFailedEmail($this->request->all());
    }
}
