<?php namespace Common\Pages;

use Auth;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Str;

class CustomPageController extends BaseController
{
    /**
     * @var CustomPage
     */
    private $page;

    /**
     * @var Request
     */
    private $request;

    /**
     * CustomPage model might get overwritten with
     * parent page model for example, LinkPage
     *
     * @param CustomPage $page
     * @param Request $request
     */
    public function __construct(CustomPage $page, Request $request)
    {
        $this->page = $page;
        $this->request = $request;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [get_class($this->page), $userId]);

        $paginator = new Paginator($this->page, $this->request->all());
        $paginator->with('user');
        if ($type = $this->request->get('type')) {
            $paginator->where('type', $type);
        }

        if ($userId) {
            $paginator->where('user_id', $userId);
        }

        $paginator->searchCallback = function(Builder $query, $term) {
            $query->where('slug', 'LIKE', "%$term%");
            $query->orWhere('body', 'LIKE', "$term%");
        };

        $pagination = $paginator->paginate();

        $pagination->transform(function($page) {
            $page->body = Str::limit(strip_tags($page->body), 50);
            return $page;
        });

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $page = $this->page->findOrFail($id);
        $this->authorize('show', $page);

        return $this->success(['page' => $page]);
    }

    /**
     * @return Response
     */
    public function store()
    {
        $this->authorize('store', get_class($this->page));

        $validatedData = $this->validate($this->request, [
            'title' => [
                'string', 'min:3', 'max:250',
                Rule::unique('custom_pages')->where('user_id', Auth::id())
            ],
            'slug' => [
                'nullable', 'string', 'min:3', 'max:250',
                Rule::unique('custom_pages'),
            ],
            'body' => "required|string|min:1",
            'hide_nav' => 'boolean',
        ]);

        $page = app(CrupdatePage::class)->execute(
            $this->page->newInstance(),
            $validatedData
        );

        return $this->success(['page' => $page]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function update($id)
    {
        $page = $this->page->findOrFail($id);
        $this->authorize('update', $page);

        $validatedData = $this->validate($this->request, [
            'title' => [
                'required', 'string', 'min:3', 'max:250',
                Rule::unique('custom_pages')->where('user_id', $page->user_id)->ignore($page->id),
            ],
            'slug' => [
                'nullable', 'string', 'min:3', 'max:250',
                Rule::unique('custom_pages')->ignore($page->id),
            ],
            'body' => "required|string|min:1",
            'hide_nav' => 'boolean',
        ]);

        $page = app(CrupdatePage::class)->execute(
            $page,
            $validatedData
        );

        return $this->success(['page' => $page]);
    }

    /**
     * @param string $ids
     * @return Response
     */
    public function destroy($ids)
    {
        $pageIds = explode(',', $ids);
        $this->authorize('destroy', [get_class($this->page), $pageIds]);

        $this->page->whereIn('id', $pageIds)->delete();

        return $this->success();
    }
}
