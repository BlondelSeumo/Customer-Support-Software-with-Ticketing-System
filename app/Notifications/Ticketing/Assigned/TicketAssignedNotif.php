<?php

namespace App\Notifications\Ticketing\Assigned;

use App\Notifications\Ticketing\TicketingNotification;
use App\Services\UrlGenerator;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TicketAssignedNotif extends TicketingNotification
{
    /**
     * @var Collection
     */
    protected $tickets;

    /**
     * @var User
     */
    protected $assigner;

    /**
     * @var User
     */
    protected $assignee;

    const NOTIF_ID = '02';

    /**
     * @param Collection $tickets
     * @param User $assigner
     * @param User $assignee
     */
    public function __construct(Collection $tickets, User $assigner, User $assignee = null)
    {
        $this->tickets = $tickets;
        $this->assigner = $assigner;
        $this->assignee = $assignee;
    }

    protected function image()
    {
        return $this->assigner->avatar;
    }

    /**
     * @return string
     */
    protected function firstLine()
    {
        $line = $this->tickets->count() === 1 ?
            "<strong>:assigner</strong> assigned :assignee conversion #:ticketId" :
            "<strong>:assigner</strong> assigned :assignee :count conversions";

        return __($line,
            [
                'assigner' => $this->assigner->display_name,
                'assignee' => $this->assignee ? $this->assignee->display_name : __('you'),
                'ticketId' => $this->tickets->first()->id,
                'count' => $this->tickets->count(),
            ]
        );
    }

    /**
     * @return string
     */
    protected function secondLine()
    {
        return Str::limit(strip_tags($this->tickets->first()->latest_reply->body), 150);
    }

    /**
     * @return array
     */
    protected function mainAction()
    {
        return [
            'label' => 'View Conversation',
            'action' => app(UrlGenerator::class)->ticket($this->tickets->first())
        ];
    }
}
