<?php namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * App\Operator
 *
 * @property integer $id
 * @property string $name
 * @method static Builder|Operator whereId($value)
 * @method static Builder|Operator whereName($value)
 * @mixin Eloquent
 * @property string $display_name
 * @property string $placeholder
 * @property string $value_type
 * @property string $value_options
 * @method static Builder|Action whereDisplayName($value)
 * @method static Builder|Action wherePlaceholder($value)
 * @method static Builder|Action whereValueType($value)
 * @method static Builder|Action whereValueOptions($value)
 * @property int $aborts_cycle
 * @property int $updates_ticket
 * @property array $input_config
 * @method static Builder|Action whereAbortsCycle($value)
 * @method static Builder|Action whereUpdatesTicket($value)
 * @method static Builder|Action whereInputConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Action query()
 */
class Action extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['id' => 'integer', 'aborts_cycle' => 'integer', 'updates_ticket' => 'integer'];

    public $timestamps  = false;

    /**
     * Getter for input_config attribute.
     *
     * @param string $value
     * @return array
     */
    public function getInputConfigAttribute($value)
    {
        return json_decode($value);
    }
}
