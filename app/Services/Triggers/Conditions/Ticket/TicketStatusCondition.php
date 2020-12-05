<?php namespace App\Services\Triggers\Conditions\Ticket;

use App\Ticket;
use App\Services\Triggers\Conditions\AbstractCondition;

class TicketStatusCondition extends AbstractCondition {

    /**
     * Check if ticket subject condition should be met based on specified values.
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
        if ( ! $updatedTicket->status) return false;

        switch ($operatorName) {
            case 'is':
                return $this->comparator->compare($updatedTicket->status, $conditionValue, 'equals');
            case 'not':
                return $this->comparator->compare($updatedTicket->status, $conditionValue, 'not_equals');
            case 'changed':
                return $this->statusChanged($updatedTicket, $originalTicket);
            case 'not_changed':
                return !$this->statusChanged($updatedTicket, $originalTicket);
            case 'changed_to':
                return $this->statusChanged($updatedTicket, $originalTicket) &&
                    $this->comparator->compare($updatedTicket->status, $conditionValue, 'equals');
            case 'not_changed_to':
                return $this->statusChanged($updatedTicket, $originalTicket) &&
                    $this->comparator->compare($updatedTicket->status, $conditionValue, 'not_equals');
            case 'changed_from':
                return $this->statusChanged($updatedTicket, $originalTicket) &&
                    $this->comparator->compare($originalTicket['status'], $conditionValue, 'equals');
            case 'not_changed_from':
                return $this->statusChanged($updatedTicket, $originalTicket) &&
                    $this->comparator->compare($originalTicket['status'], $conditionValue, 'not_equals');
        }

        return false;
    }

    /**
     * @param Ticket $updatedTicket
     * @param array $originalTicket
     * @return bool
     */
    protected function statusChanged(Ticket $updatedTicket, $originalTicket) {
        dd($originalTicket, $updatedTicket);
        return $originalTicket && $this->comparator->compare($updatedTicket->status, $originalTicket['status'], 'not_equals');
    }
}
