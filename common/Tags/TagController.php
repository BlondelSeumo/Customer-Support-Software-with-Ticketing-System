<?php

namespace Common\Tags;

use App\Tag as AppTag;
use Common\Core\BaseController;
use Common\Database\Paginator;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', Tag::class);

        $paginator = (new Paginator($this->getModel(), $this->request->all()));

        if ($type = $paginator->param('type')) {
            $paginator->where('type', $type);
        }

        if ($notType = $paginator->param('notType')) {
            $paginator->where('type', '!=', $notType);
        }

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * @return JsonResponse
     */
    public function store()
    {
        $this->authorize('store', Tag::class);

        $this->validate($this->request, [
            'name' => 'required|string|min:2|unique:tags',
            'display_name' => 'string|min:2',
            'type' => 'required|string|min:2',
        ]);

        $tag = $this->getModel()->create([
            'name' => $this->request->get('name'),
            'display_name' => $this->request->get('display_name'),
            'type' => $this->request->get('type'),
        ]);

        return $this->success(['tag' => $tag]);
    }

    /**
     * @param int $tagId
     * @return JsonResponse
     */
    public function update($tagId)
    {
        $this->authorize('update', Tag::class);

        $this->validate($this->request, [
            'name' => "string|min:2|unique:tags,name,$tagId",
            'display_name' => 'string|min:2',
            'type' => 'string|min:2',
        ]);

        $tag = $this->getModel()->findOrFail($tagId);

        $tag->fill($this->request->all())->save();

        return $this->success(['tag' => $tag]);
    }

    /**
     * @param string $ids
     * @return JsonResponse
     */
    public function destroy($ids)
    {
        $tagIds = explode(',', $ids);
        $this->authorize('destroy', [Tag::class, $tagIds]);

        $this->getModel()->whereIn('id', $tagIds)->delete();
        DB::table('taggables')->whereIn('tag_id', $tagIds)->delete();

        return $this->success();
    }

    /**
     * @return Tag
     */
    protected function getModel()
    {
        return $tag = app(class_exists(AppTag::class) ? AppTag::class : Tag::class);
    }
}
