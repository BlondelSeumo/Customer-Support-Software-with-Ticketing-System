<?php namespace App\Services\Triggers;

use App\Ticket;
use App\Trigger;
use App\Services\Triggers\Actions\Actions;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Triggers\Conditions\Conditions;

class TriggersCycle {

    /**
     * Trigger repository instance.
     *
     * @var TriggerRepository
     */
    private $repository;

    /**
     * Actions service instance.
     *
     * @var Actions
     */
    private $actions;

    /**
     * Conditions service instance.
     *
     * @var Conditions
     */
    private $conditions;

    /**
     * IDs of triggers that have already fired.
     *
     * @var array
     */
    private $triggersToSkip = [];

    /**
     * Total number of times triggers cycle looped .
     *
     * @var int
     */
    private $timesLooped = 0;

    /**
     * Total number of times triggers have fired during this cycle.
     *
     * @var int
     */
    private $triggersFired = 0;

    /**
     * TriggersCycle constructor.
     *
     * @param TriggerRepository $repository
     * @param Actions $actions
     * @param Conditions $conditions
     */
    public function __construct(TriggerRepository $repository, Actions $actions, Conditions $conditions)
    {
        $this->repository = $repository;
        $this->actions    = $actions;
        $this->conditions = $conditions;
    }

    /**
     * Run triggers cycle again specified ticket.
     *
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     *
     * @return array
     */
    public function runAgainstTicket(Ticket $updatedTicket, $originalTicket = null)
    {
        $triggers = $this->repository->all();

        $this->runCycle($triggers, $updatedTicket, $originalTicket);

        $response = [
            'timesFired'  => $this->triggersFired,
            'timesLooped' => $this->timesLooped
        ];

        $this->triggersToSkip = [];
        $this->timesLooped = 0;
        $this->triggersFired = 0;

        return $response;
    }

    /**
     * Triggers cycle will run every trigger against a ticket.
     * If trigger fires and action updates ticket, the cycle will run again
     * skipping triggers that were already checked (regardless of them actually firing)
     *
     * @param array|Collection $triggers
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     *
     * @return void
     */
    private function runCycle($triggers, Ticket $updatedTicket, $originalTicket = null)
    {
        foreach($triggers as $trigger) {
            $this->timesLooped++;

            if ($this->triggerShouldFire($trigger, $updatedTicket, $originalTicket)) {
                $result = $this->fireTrigger($trigger, $updatedTicket, $originalTicket);

                if ($result['command'] === 'abort') {
                    break;
                } else if ($result['command'] === 'continue') {
                    continue;
                } else if ($result['command'] === 'restart') {
                    $this->runCycle($triggers, $result['ticket'], $originalTicket);
                    break;
                }
            }
        }
    }

    /**
     * 'Fire' specified trigger on ticket.
     *
     * @param Trigger $trigger
     * @param Ticket $updatedTicket
     * @param array|null $originalTicket
     * @return array
     */
    private function fireTrigger($trigger, Ticket $updatedTicket, $originalTicket)
    {
        $trigger->increment('times_fired');

        $updatedTicket = $this->actions->perform($updatedTicket, $trigger);

        //mark this trigger as already 'fired' so we don't fire same triggers twice
        $this->triggersToSkip[] = $trigger->id;

        //if one of this triggers actions updates ticket or
        //one of its relationships, we need to run all triggers
        //against updated ticket again, except triggers that already fired
        if ($this->actions->updatesTicket($trigger->actions)) {
            $command = 'restart';
        }

        //if one of this triggers actions aborts triggers
        //cycle (for example 'delete_ticket' action), bail
        if ($this->actions->abortsCycle($trigger->actions)) $command = 'abort';

        $this->triggersFired++;

        return ['command' => isset($command) ? $command : 'continue', 'ticket' => $updatedTicket];
    }

    /**
     * Determine if given trigger should fire based on specified arguments.
     *
     * @param Trigger     $trigger
     * @param Ticket      $updatedTicket
     * @param array|null $originalTicket
     * @return bool
     */
    private function triggerShouldFire(Trigger $trigger, Ticket $updatedTicket, $originalTicket)
    {
        //if this trigger has already been fired, continue to next trigger
        if (in_array($trigger->id, $this->triggersToSkip)) return false;

        return $this->conditions->areMet($trigger, $updatedTicket, $originalTicket);
    }
}
