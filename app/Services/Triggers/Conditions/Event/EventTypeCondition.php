<?php

namespace App\Services\Triggers\Conditions\Event;

use App\Services\Triggers\Conditions\AbstractCondition;
use App\Ticket;

class EventTypeCondition extends AbstractCondition
{
    /**
     * Check if ticket was created or updated.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @param string $operatorName
     * @param mixed $conditionValue
     *
     * @return bool
     */
    public function isMet(Ticket $updatedTicket, $originalTicket, $operatorName, $conditionValue)
    {
        if ($operatorName === 'is') {
            if ($conditionValue === 'ticket_created' && !$originalTicket) {
                return true;
            } else if ($conditionValue === 'ticket_updated' && $originalTicket) {
                return true;
            }
        } else if ($operatorName === 'not') {
            if ($conditionValue === 'ticket_created' && $originalTicket) {
                return true;
            } else if ($conditionValue === 'ticket_updated' && !$originalTicket) {
                return true;
            }
        }

        return false;
    }
}
