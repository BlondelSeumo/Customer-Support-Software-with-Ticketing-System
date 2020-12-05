<?php namespace App\Http\Controllers;

use App\Category;
use App\Http\Requests\ModifyCategories;
use App\User;
use Auth;
use Common\Core\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends BaseController {

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Category
	 */
	private $category;

	/**
	 * @var User|null
	 */
	private $user;

	public function __construct(Request $request, Category $category)
	{
		$this->request  = $request;
		$this->category = $category;
		$this->user     = Auth::user();
	}

	public function index()
	{
	    $this->authorize('index', Category::class);

		$query = $this->category->rootOnly()
            ->withCount('articles')
            ->with(['children' => function($query) {
                $query->withCount('articles');
            }])
            ->orderByPosition();

		if ($search = $this->request->get('query')) {
		    $query->where('name', 'like', "%$search%");
        }

		if ($limit = $this->request->get('limit')) {
		    $query->limit($limit);
        }

		$categories = $query->get();

		return $this->success(['categories' => $categories]);
	}

    public function show(int $id)
    {
        $this->authorize('show', Category::class);

        $category = $this->category->with('children', 'parent.children')->findOrFail($id);

        return $this->success(['category' => $category]);
    }

	public function store(ModifyCategories $request)
	{
		$this->authorize('store', Category::class);

		$last = $this->category->orderBy('position', 'desc')->first();

		$category = $this->category->create([
			'name'        => $this->request->get('name'),
			'description' => $this->request->get('description'),
			'parent_id'   => $this->request->get('parent_id', null),
			'position'    => $last ? $last->position + 1 : 1,
		]);

        cache()->forget(HelpCenterController::HC_HOME_CACHE_KEY);

		return response($category, 201);
	}

	public function update(int $id, ModifyCategories $request)
	{
		$this->authorize('update', Category::class);

		$category = $this->category->findOrFail($id);

		$category->fill($this->request->all())->save();

        cache()->forget(HelpCenterController::HC_HOME_CACHE_KEY);

		return $category;
	}

	public function destroy(int $id)
	{
        $this->authorize('destroy', Category::class);

	    $category = $this->category->findOrFail($id);

		$category->where('parent_id', $category->id)->update(['parent_id' => null]);
		$category->delete();

        cache()->forget(HelpCenterController::HC_HOME_CACHE_KEY);

		return $this->success(['data' => $category->id], 204);
	}
}
