<?php namespace App\Services\Triggers\Conditions;

use App;
use App\Ticket;
use App\Trigger;
use App\Condition;
use Illuminate\Support\Collection;

class Conditions {

    /**
     * Check if specified ticket meets triggers conditions. If true, trigger action should be fired.
     *
     * @param Trigger $trigger
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     *
     * @return bool
     */
    public function areMet(Trigger $trigger, Ticket $updatedTicket, $originalTicket = null)
    {
        if ($trigger->conditions->isEmpty()) return false;

        return $this->conditionsWithTypeAllAreMet($trigger->conditions, $updatedTicket, $originalTicket) &&
               $this->conditionsWithTypeAnyAreMet($trigger->conditions, $updatedTicket, $originalTicket);
    }


    /**
     * Check if all conditions with match type 'all' are met.
     *
     * @param Collection $conditions
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @return bool
     */
    private function conditionsWithTypeAllAreMet($conditions, $updatedTicket, $originalTicket)
    {
        $conditions = $this->filterConditionsByMatchType($conditions, 'all');

        if ($conditions->isEmpty()) return true;

        foreach($conditions as $condition) {
            if ( ! $this->conditionIsMet($updatedTicket, $originalTicket, $condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if either of conditions with match type 'any' are met.
     *
     * @param Collection $conditions
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @return bool
     */
    private function conditionsWithTypeAnyAreMet($conditions, $updatedTicket, $originalTicket)
    {
        $conditions = $this->filterConditionsByMatchType($conditions, 'any');

        if ($conditions->isEmpty()) return true;

        foreach($conditions as $condition) {
            if ($this->conditionIsMet($updatedTicket, $originalTicket, $condition)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if ticket meets specified condition.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @param Condition $condition
     *
     * @return bool
     */
    public function conditionIsMet(Ticket $updatedTicket, $originalTicket, Condition $condition)
    {
        $operator = $condition['selected_operator'];
        $value    = $condition['pivot']['condition_value'];

        return $this->getCondition($condition->type)->isMet(
            $updatedTicket, $originalTicket, $operator['name'], $value
        );
    }

    /**
     * Filter conditions by specified match type.
     *
     * @param Collection $conditions
     * @param string $matchType
     * @return Collection
     */
    private function filterConditionsByMatchType($conditions, $matchType)
    {
        return $conditions->filter(function($condition) use($matchType) {
            return $condition['pivot']['match_type'] === $matchType;
        });
    }

    /**
     * Get instance of specified condition class.
     *
     * @param string $conditionType
     * @return AbstractCondition
     */
    private function getCondition($conditionType)
    {
        $className = $this->getConditionClassName($conditionType);

        $folder = ucfirst(explode(':', $conditionType)[0]);

        return App::make('App\Services\Triggers\Conditions\\'.$folder.'\\'.$className);
    }

    /**
     * Get class name of specified condition.
     *
     * @param string $conditionType
     * @return string
     */
    private function getConditionClassName($conditionType)
    {
        $parts = explode(':', $conditionType);
        $parts[1] = ucfirst($parts[1]);

        return ucfirst(implode('', $parts)).'Condition';
    }
}