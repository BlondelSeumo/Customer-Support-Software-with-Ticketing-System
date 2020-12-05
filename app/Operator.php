<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Operator
 *
 * @property integer $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $display_name
 * @property string $value_type
 * @property string $value_placeholder
 * @property string $validation_rules
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereDisplayName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereValueType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereValuePlaceholder($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereValidationRules($value)
 * @property string $type
 * @method static \Illuminate\Database\Query\Builder|\App\Operator whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Operator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Operator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Operator query()
 */
class Operator extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['id' => 'integer'];

    public $timestamps  = false;
}
