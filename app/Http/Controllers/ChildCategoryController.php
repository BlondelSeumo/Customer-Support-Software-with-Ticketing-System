<?php namespace App\Http\Controllers;

use Auth;
use App\Category;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class ChildCategoryController extends BaseController {

	/**
	 * Laravel request instance.
	 *
	 * @var Request
	 */
	private $request;

	/**
	 * Help Center category model instance.
	 *
	 * @var Category
	 */
	private $category;

	/**
	 * Logged in user instance or null.
	 *
	 * @var \App\User|null
	 */
	private $user;

	public function __construct(Request $request, Category $category)
	{
		$this->request  = $request;
		$this->category = $category;
		$this->user     = Auth::user();
	}

    /**
     * Detach specified category from its parent.
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     */
	public function detachParent($id)
    {
        $this->authorize('update', Category::class);

        $category = $this->category->where('id', $id)->update(['parent_id' => null]);

        return $this->success(['data' => $category]);
    }
}
