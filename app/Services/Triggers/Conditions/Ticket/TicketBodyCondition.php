<?php namespace App\Services\Triggers\Conditions\Ticket;

use App\Ticket;
use App\Services\Triggers\Conditions\AbstractCondition;

class TicketBodyCondition extends AbstractCondition {

    /**
     * Check if ticket body condition should be met based on specified values.
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
        if ( ! $updatedTicket->latest_reply) return false;

        return $this->comparator->compare($updatedTicket->latest_reply->body, $conditionValue, $operatorName);
    }
}