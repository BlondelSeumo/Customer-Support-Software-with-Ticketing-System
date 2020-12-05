<?php namespace App\Services\Triggers;

use Arr;
use Auth;
use DB;
use App\Operator;
use App\Trigger;
use Illuminate\Database\Eloquent\Collection;

class TriggerRepository {

    /**
     * Trigger model.
     *
     * @var Trigger
     */
    private $trigger;

    public function __construct(Trigger $trigger, Operator $operator)
    {
        $this->trigger = $trigger;
        $this->operator = $operator;
    }

    /**
     * Fetch specified trigger.
     *
     * @param integer $id
     * @return Trigger
     */
    public function findOrFail($id)
    {
        $trigger = $this->trigger->with(['conditions.operators', 'actions'])->findOrFail($id);

        $trigger = $this->attachSelectedOperator([$trigger])[0];

        return $trigger;
    }

    /**
     * Return all existing triggers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        $triggers = $this->trigger->with('conditions', 'actions')->get();

        return $this->attachSelectedOperator($triggers);
    }

    /**
     * Create a new trigger.
     *
     * @param array $params
     * @return Trigger
     */
    public function create($params)
    {
        $trigger = $this->trigger->create([
            'name'        => $params['name'],
            'description' => $params['description'] ?? null,
            'times_fired'  => $params['times_fired'] ?? 0,
            'created_at'  => $params['created'] ?? null,
            'user_id' => Auth::id(),
        ]);

        // might need to insert multiple of same condition so can't use
        // laravel multi-attach method as it won't allow duplicate IDs
        $conditionInsert = array_map(function($data) use($trigger) {
            return [
                'condition_value' => $data['value'],
                'match_type' => $data['matchType'],
                'operator_id' => $data['operator_id'],
                'condition_id' => $data['condition_id'],
                'trigger_id' => $trigger['id'],
            ];
        }, $params['conditions']);
        // make sure we have no duplicates
        $conditionInsert = array_map('unserialize', array_unique(array_map('serialize', $conditionInsert)));
        $trigger->conditions()->newPivotStatement()->insert($conditionInsert);

        //attach actions to trigger
        $actionInsert = array_map(function($data) use($trigger) {
            return [
                'action_id' => $data['action_id'],
                'action_value' => json_encode($data['value']),
                'trigger_id' => $trigger['id'],
            ];
        }, $params['actions']);
        $actionInsert = array_map('unserialize', array_unique(array_map('serialize', $actionInsert)));
        $trigger->actions()->newPivotStatement()->insert($actionInsert);

        return $trigger;
    }

    /**
     * Update trigger matching specified id.
     *
     * @param integer $id
     * @param array $params
     *
     * @return Trigger
     */
    public function update($id, $params)
    {
        $trigger = $this->findOrFail($id);

        //prepare params
        $params['times_fired'] = $trigger->times_fired;
        $params['created_at'] = $trigger->created_at;

        //delete old trigger
        $this->delete([$id]);

        //create new trigger
        return $this->create($params);
    }

    /**
     * Delete triggers matching specified ids.
     *
     * @param integer[] $ids
     * @return integer
     */
    public function delete($ids)
    {
        DB::table('trigger_condition')->whereIn('trigger_id', $ids)->delete();
        DB::table('trigger_action')->whereIn('trigger_id', $ids)->delete();

        return $this->trigger->whereIn('id', $ids)->delete();
    }

    /**
     * Attach selected operator to each of specified triggers conditions
     *
     * @param array $triggers
     * @return array|Collection
     */
    private function attachSelectedOperator($triggers)
    {
        $operatorsIds = collect();

        foreach($triggers as $trigger) {
            $operatorsIds = $operatorsIds->merge($trigger->conditions->map(function($condition) {
                return $condition->pivot->operator_id;
            }));
        }

        $operators = $this->operator->whereIn('id', $operatorsIds)->get();

        foreach($triggers as $trigger) {
            $trigger->conditions->each(function($condition) use($operators) {
                $condition->selected_operator = $operators->find($condition->pivot->operator_id);
            });
        }

        return $triggers;
    }

}
