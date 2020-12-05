<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Operator
 *
 * @property integer $id
 * @property string $name
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereName($value)
 * @mixin \Eloquent
 * @property string $description
 * @property integer $times_fired
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Condition[] $conditions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Action[] $actions
 * @property integer user_id
 */
class Trigger extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'times_fired' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Conditions that are attached to this trigger.
     */
    public function conditions()
    {
        return $this->belongsToMany(Condition::class, 'trigger_condition')->withPivot(['condition_value', 'match_type', 'operator_id']);
    }

    /**
     * Actions that are attached to this trigger.
     */
    public function actions()
    {
        return $this->belongsToMany(Action::class, 'trigger_action')->withPivot(['action_value']);
    }

    public function selectedOperator()
    {

    }
}
