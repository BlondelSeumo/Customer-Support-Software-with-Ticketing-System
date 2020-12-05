<?php namespace App\Services\Triggers\Actions;

use App\Action;
use App\Ticket;
use App\Trigger;

interface TriggerActionInterface {

    /**
     * Perform specified action on ticket.
     *
     * @param Ticket $ticket
     * @param Action $action
     * @param Trigger $trigger
     * @return Ticket
     */
    public function perform(Ticket $ticket, Action $action, Trigger $trigger);
}
