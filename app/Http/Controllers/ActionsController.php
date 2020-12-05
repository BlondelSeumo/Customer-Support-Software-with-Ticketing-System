<?php namespace App\Http\Controllers;

use App\Action;
use Illuminate\Database\Eloquent\Collection;
use Common\Core\BaseController;

class ActionsController extends BaseController
{
    /**
     * Action model instance.
     *
     * @var Action $action
     */
    private $action;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    /**
     * Get all available triggers actions.
     *
     * @return Collection
     */
    public function index()
    {
        $this->authorize('index', Action::class);

        return $this->action->orderBy('name', 'asc')->get();
    }
}
