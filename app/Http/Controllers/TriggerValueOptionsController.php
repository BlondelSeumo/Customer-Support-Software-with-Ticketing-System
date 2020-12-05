<?php namespace App\Http\Controllers;

use App\Trigger;
use App\Services\Triggers\ValueOptions\ValueOptions;
use Common\Core\BaseController;

class TriggerValueOptionsController extends BaseController
{

    /**
     * ValueOptions service instance.
     *
     * @var ValueOptions
     */
    private $valueOptions;

    public function __construct(ValueOptions $valueOptions)
    {
        $this->valueOptions = $valueOptions;
    }

    /**
     * Get select options for trigger condition/action value.
     *
     * @param string $name
     * @return mixed
     */
    public function show($name)
    {
        $this->authorize('store', Trigger::class);

        return $this->success(['data' => $this->valueOptions->getByName($name)]);
    }
}
