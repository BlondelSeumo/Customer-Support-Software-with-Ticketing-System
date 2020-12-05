<?php namespace App\Services\Triggers\Conditions\Ticket;

use App\Ticket;
use App\Services\Triggers\Conditions\AbstractCondition;

class TicketCategoryCondition extends AbstractCondition {

    /**
     * Check if ticket categories condition should be met based on specified values.
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
        foreach ($updatedTicket->categories as $category) {
            if ($this->comparator->compare($category->name, $conditionValue, $operatorName)) {
                return true;
            }
        }

        return false;
    }
}