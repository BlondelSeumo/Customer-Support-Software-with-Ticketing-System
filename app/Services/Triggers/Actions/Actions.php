<?php namespace App\Services\Triggers\Actions;

use App;
use App\Ticket;
use App\Trigger;
use Illuminate\Support\Str;

class Actions {

    /**
     * Perform all trigger actions on specified ticket.
     *
     * @param Ticket $ticket
     * @param Trigger $trigger
     *
     * @return Ticket
     */
    public function perform(Ticket $ticket, Trigger $trigger)
    {
        foreach($trigger->actions as $actionModel) {
            $action = $this->getAction($actionModel->name);

            $ticket = $action->perform($ticket, $actionModel, $trigger);

            //if action aborts triggers cycle (for example deletes ticket)
            //we need to bail instantly and not run any more actions after it
            if ($this->abortsCycle([$actionModel])) break;
        }

        return $ticket;
    }

    /**
     * Check if any of specified actions updates ticket.
     *
     * @param array $actions
     * @return bool
     */
    public function updatesTicket($actions)
    {
        foreach($actions as $action) {
            if ($action['updates_ticket'] === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if any of specified actions aborts triggers/actions cycles.
     *
     * @param array $actions
     * @return bool
     */
    public function abortsCycle($actions)
    {
        foreach($actions as $action) {
            if ($action['aborts_cycle'] === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get an instance of specified action class.
     *
     * @param string $actionName
     * @return TriggerActionInterface
     */
    protected function getAction($actionName)
    {
        $className = $this->getActionClassName($actionName);

        return App::make('App\Services\Triggers\Actions\\'.$className);
    }

    /**
     * Get class name of specified action.
     *
     * @param string $actionName
     * @return string
     */
    protected function getActionClassName($actionName)
    {
        return ucfirst(Str::camel($actionName)).'Action';
    }
}
