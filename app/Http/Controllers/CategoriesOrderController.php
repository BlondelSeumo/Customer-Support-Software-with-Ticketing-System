<?php namespace App\Http\Controllers;

use DB;
use App\Category;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class CategoriesOrderController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Category
     */
    private $category;

    /**
     * @param Request $request
     * @param Category $category
     */
    public function __construct(Request $request, Category $category)
    {
        $this->request  = $request;
        $this->category = $category;
    }

    /**
     * Change categories order based on their index in specified IDs array.
     */
    public function change()
    {
        $this->authorize('update', Category::class);

        $this->validate($this->request, [
            'parentId' => 'integer|nullable',
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer'
        ]);

        $parentId =  $this->request->get('parentId');

        $queryPart = '';
        foreach($this->request->get('ids') as $position => $id) {
            $position++;
            $queryPart .= " when id=$id then $position";
        }

        $this->category
            ->query()
            ->where('parent_id', $parentId ?: null)
            ->update(['position' => DB::raw("(case $queryPart end)")]);

        return $this->success([], 200);
    }
}
