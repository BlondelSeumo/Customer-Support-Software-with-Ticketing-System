<?php namespace App\Listeners;

use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use App\Services\Triggers\TriggersCycle;
use App\Ticket;
use Common\Settings\Settings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Mail\Mailer;

class TicketEventSubscriber implements ShouldQueue
{
    /**
     * @var TriggersCycle
     */
    private $triggersCycle;

    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param TriggersCycle $triggersCycle
     * @param Mailer $mailer
     * @param Settings $settings
     */
    public function __construct(TriggersCycle $triggersCycle, Mailer $mailer, Settings $settings)
    {
        $this->mailer = $mailer;
        $this->settings = $settings;
        $this->triggersCycle = $triggersCycle;
    }

    /**
     * @param TicketCreated $event
     */
    public function onTicketCreated(TicketCreated $event)
    {
        $ticket = app(Ticket::class)->find($event->ticket->id);
        $this->triggersCycle->runAgainstTicket($ticket);

        if ($this->settings->get('tickets.send_ticket_created_notification')) {
            $ticket->user->notify(new \App\Notifications\TicketReceived($ticket));
        }
    }

    /**
     * @param TicketUpdated $event
     */
    public function onTicketUpdated(TicketUpdated $event)
    {
        $this->triggersCycle->runAgainstTicket($event->updatedTicket, $event->originalTicket);
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\TicketCreated',
            'App\Listeners\TicketEventSubscriber@onTicketCreated'
        );

        $events->listen(
            'App\Events\TicketUpdated',
            'App\Listeners\TicketEventSubscriber@onTicketUpdated'
        );
    }

}
