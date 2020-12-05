<?php namespace App\Services\Mail;

use App;
Use App\Ticket;
use Common\Auth\UserRepository;
use Common\Settings\Settings;
use App\Services\Ticketing\TicketRepository;
use Arr;

class FailedMailHandler
{
    /**
     * TicketRepository instance.
     *
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ParsedEmail
     */
    private $parsedEmail;

    public function __construct(
        TicketRepository $ticketRepository,
        Settings $settings,
        UserRepository $userRepository,
        ParsedEmail $parsedEmail
    )
    {
        $this->settings = $settings;
        $this->parsedEmail = $parsedEmail;
        $this->userRepository = $userRepository;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Create a new ticket notifying agents of failed email delivery.
     *
     * @param array $emailData
     * @return Ticket
     */
    public function createTicketForFailedEmail($emailData)
    {
        $user = $this->userRepository->firstOrCreate(['email' => $this->getPostMasterEmail()]);

        $data = [
            'reason'      => $emailData['reason'],
            'description' => Arr::get($emailData, 'description', ''),
            'recipient'   => $emailData['recipient'],
            'headers'     => $this->getHeaders($emailData),
        ];

        return $this->ticketRepository->create([
            'body'    => view('tickets.failed-email-ticket-body')->with($data)->render(),
            'subject' => 'Failed Email Delivery Report',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Convert email headers array into string.
     *
     * @param array $emailData
     * @return string
     */
    private function getHeaders($emailData)
    {
        $headers = [];

        $this->parsedEmail->setEmailData($emailData);

        foreach ($this->parsedEmail->getHeaders() as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        return implode(PHP_EOL, $headers);
    }

    /**
     * Get email address for PostMater user.
     *
     * @return string
     */
    private function getPostMasterEmail()
    {
        $host = parse_url(config('app.url'))['host'];

        return "postmaster@$host";
    }
}
