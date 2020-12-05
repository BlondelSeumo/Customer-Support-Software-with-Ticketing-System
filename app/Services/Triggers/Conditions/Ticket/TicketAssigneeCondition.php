<?php namespace App\Services\Triggers\Conditions\Ticket;

use App\Ticket;
use App\Services\Triggers\Conditions\AbstractCondition;
use App\User;
use Str;

class TicketAssigneeCondition extends AbstractCondition {

    /**
     * Check if ticket assignee condition is met using specified values.
     *
     * @uses assigneeIs, assigneeNot, assigneeChanged, assigneeChangedTo,
     * @uses assigneeChangedFrom, assigneeNotChanged, assigneeNotChangedTo, assigneeNotChangedFrom
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
        if ( ! $originalTicket) return false;

        $methodName = 'assignee'.ucfirst(Str::camel($operatorName));

        // if conditionValue is email, fetch matching user ID
        if (is_string($conditionValue) && Str::contains($conditionValue, '@')) {
            $user = app(User::class)->where('email', $conditionValue)->first();
            $conditionValue = $user ? $user->id : null;
        }

        //cast conditionValue to integer, unless it's falsy,
        //null for example means ticket is unassigned currently
        $conditionValue = $conditionValue ? (int) $conditionValue : $conditionValue;

        return $this->$methodName($updatedTicket, $originalTicket, $conditionValue);
    }

    /**
     * Test if ticket assignee matches specified value.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @param mixed $conditionValue
     *
     * @return bool
     */
    protected function assigneeIs(Ticket $updatedTicket, $originalTicket, $conditionValue)
    {
        return $this->comparator->compare($updatedTicket->assigned_to, $conditionValue, 'equals');
    }

    /**
     * Test if ticket assignee does not match specified value.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @param mixed $conditionValue
     *
     * @return bool
     */
    protected function assigneeNot(Ticket $updatedTicket, $originalTicket, $conditionValue)
    {
        return ! $this->assigneeIs($updatedTicket, $originalTicket, $conditionValue);
    }

    /**
     * Test if ticket assignee changed.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     *
     * @return bool
     */
    protected function assigneeChanged(Ticket $updatedTicket, $originalTicket)
    {
        return $this->comparator->compare($updatedTicket->assigned_to, $originalTicket['assigned_to'], 'not_equals');
    }

    /**
     * Test if ticket assignee did not change.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     *
     * @return bool
     */
    protected function assigneeNotChanged($updatedTicket, $originalTicket)
    {
        return ! $this->assigneeChanged($updatedTicket, $originalTicket);
    }

    /**
     * Test if ticket assignee changed to specific value.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @param mixed $conditionValue
     *
     * @return bool
     */
    protected function assigneeChangedTo($updatedTicket, $originalTicket, $conditionValue)
    {
        return $this->assigneeChanged($updatedTicket, $originalTicket) &&
               $this->comparator->compare($updatedTicket->assigned_to, $conditionValue, 'equals');
    }

    /**
     * Test if ticket assignee changed from specific value.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @param mixed $conditionValue
     *
     * @return bool
     */
    protected function assigneeChangedFrom($updatedTicket, $originalTicket, $conditionValue)
    {
        return $this->assigneeChanged($updatedTicket, $originalTicket) &&
               $this->comparator->compare($originalTicket['assigned_to'], $conditionValue, 'equals');
    }

    /**
     * Test if ticket assignee did not change to specific value.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @param mixed $conditionValue
     *
     * @return bool
     */
    protected function assigneeNotChangedTo($updatedTicket, $originalTicket, $conditionValue)
    {
        return $this->assigneeChanged($updatedTicket, $originalTicket) &&
               $this->comparator->compare($updatedTicket->assigned_to, $conditionValue, 'not_equals');
    }

    /**
     * Test if ticket assignee did not change from specific value.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @param mixed $conditionValue
     *
     * @return bool
     */
    protected function assigneeNotChangedFrom($updatedTicket, $originalTicket, $conditionValue)
    {
        return $this->assigneeChanged($updatedTicket, $originalTicket) &&
               $this->comparator->compare($originalTicket['assigned_to'], $conditionValue, 'not_equals');
    }
}
