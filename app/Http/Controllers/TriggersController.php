<?php namespace App\Http\Controllers;

use App\Trigger;
use Common\Database\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ModifyTriggers;
use App\Services\Triggers\TriggerRepository;
use Common\Core\BaseController;
use Illuminate\Http\Response;

class TriggersController extends BaseController
{
    /**
     * @var TriggerRepository $trigger
     */
    private $repository;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param TriggerRepository $repository
     * @param Request $request
     */
    public function __construct(TriggerRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request = $request;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', Trigger::class);

        $paginator = (new Paginator(app(Trigger::class), $this->request->all()));

        $paginator->searchCallback = function(Builder $builder, $query) {
            $builder->where('name', 'LIKE', "%$query%")
                ->orWhere('description', 'LIKE', "$query%");
        };

        return $this->success(['pagination' => $paginator->paginate()]);
    }

    /**
     * @param integer $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $this->authorize('index', Trigger::class);

        return $this->success(['data' => $this->repository->findOrFail($id)]);
    }

    /**
     * @param ModifyTriggers $request
     * @return JsonResponse
     */
    public function store(ModifyTriggers $request)
    {
        $this->authorize('store', Trigger::class);

        return response($this->repository->create($this->request->all()), 201);
    }

    /**
     * @param integer $id
     * @param ModifyTriggers $request
     *
     * @return Trigger
     */
    public function update($id, ModifyTriggers $request)
    {
        $this->authorize('update', Trigger::class);

        return $this->repository->update($id, $this->request->all());
    }

    /**
     * @return Response
     */
    public function destroy()
    {
        $this->authorize('destroy', Trigger::class);

        $this->validate($this->request, [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer'
        ]);

        return response($this->repository->delete($this->request->get('ids')), 204);
    }
}
