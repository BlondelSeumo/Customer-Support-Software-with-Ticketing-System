<?php namespace App\Services\Triggers\Conditions\Customer;

use App\Ticket;
use App\Services\Triggers\Conditions\AbstractCondition;

class CustomerEmailCondition extends AbstractCondition {

    /**
     * Check if ticket customer condition should be met based on specified values.
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
        return $this->comparator->compare($updatedTicket->user->email, $conditionValue, $operatorName);
    }
}