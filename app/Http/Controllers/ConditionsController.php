<?php namespace App\Http\Controllers;

use App\Condition;
use Illuminate\Database\Eloquent\Collection;
use Common\Core\BaseController;

class ConditionsController extends BaseController
{
    /**
     * Condition model instance.
     *
     * @var Condition
     */
    private $condition;

    /**
     * ConditionsController constructor.
     *
     * @param Condition $condition
     */
    public function __construct(Condition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * Get all available triggers conditions.
     *
     * @return Collection
     */
    public function index()
    {
        $this->authorize('index', Condition::class);

        return $this->condition->with('operators')->get();
    }
}
