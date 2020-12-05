<?php namespace App\Services\Triggers\Conditions;

use App\Ticket;

abstract class AbstractCondition {

    /**
     * PrimitiveValuesComparator service instance.
     *
     * @var PrimitiveValuesComparator
     */
    protected $comparator;

    /**
     * TicketAssigneeCondition constructor.
     *
     * @param PrimitiveValuesComparator $comparator
     */
    public function __construct(PrimitiveValuesComparator $comparator)
    {
        $this->comparator = $comparator;
    }

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
    abstract public function isMet(Ticket $updatedTicket, $originalTicket, $operatorName, $conditionValue);
}