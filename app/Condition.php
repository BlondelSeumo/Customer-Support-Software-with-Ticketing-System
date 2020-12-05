<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Condition
 *
 * @property integer $id
 * @property string $name
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Operator[] $operators
 * @method static \Illuminate\Database\Query\Builder|\App\Condition whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Condition whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Condition whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Condition whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Condition whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $type
 * @method static \Illuminate\Database\Query\Builder|\App\Condition whereType($value)
 * @property-read array $input_config
 * @property-read int|null $operators_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Condition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Condition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Condition query()
 */
class Condition extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['id' => 'integer'];

    public $timestamps = false;

    /**
     *  Operators that are attached to this condition.
     */
    public function operators()
    {
        return $this->belongsToMany(Operator::class);
    }

    /**
     * @param string $value
     * @return array
     */
    public function getInputConfigAttribute($value)
    {
        return json_decode($value);
    }
}
