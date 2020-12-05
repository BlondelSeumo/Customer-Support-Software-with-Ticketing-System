<?php namespace App\Http\Controllers;

use App\Category;
use App\Services\Mail\IncomingMailHandler;
use Cache;
use Carbon\Carbon;
use Common\Core\BaseController;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelpCenterController extends BaseController
{
    const HC_HOME_CACHE_KEY = 'hc.home';

    /**
     * @var Category $category
     */
    private $category;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Category $category
     * @param Settings $settings
     * @param Request $request
     */
    public function __construct(Category $category, Settings $settings, Request $request)
    {
        $this->category = $category;
        $this->settings = $settings;
        $this->request = $request;
    }

    /**
     * Return all help center categories, child categories and articles.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', Category::class);

        $data = $this->getHelpCenterData();

        $options = [
            'prerender' => [
                'view' => "home.show",
                'config' => "home.show",
            ]
        ];

        return $this->success(['categories' => $data], 200, $options);
    }

    /**
     * Return data for rendering help center sidebar navigation.
     *
     * @return JsonResponse
     */
    public function sidenav()
    {
        $this->authorize('index', Category::class);

        $parentCategoryId = $this->request->get('categoryId');

        $data = $this->category
            ->where('parent_id', $parentCategoryId)
            ->with(['articles' => function(BelongsToMany $query) {
                $query->select('id', 'title', 'position', 'slug');
            }])
            ->orderByPosition()
            ->limit(10)
            ->get();


        return $this->success(['categories' => $data]);
    }

    private function getHelpCenterData()
    {
        return Cache::remember(self::HC_HOME_CACHE_KEY, Carbon::now()->addDays(2), function() {

            $loadArticles = ['articles' => function(BelongsToMany $query) {
                $query->select('id', 'title', 'position', 'slug');
            }];

            //load categories with children and articles
            $categories = $this->category
                ->rootOnly()
                ->where('hidden', false)
                ->orderByPosition()
                ->limit(10)
                ->withCount('children')
                ->with($loadArticles)
                ->with(['children' => function(HasMany $query) use($loadArticles) {
                    $query->where('hidden', false)->withCount('articles')->with($loadArticles);
                }])->get();

            $categoryLimit = $this->settings->get('hc_home.children_per_category', 6);
            $articleLimit = $this->settings->get('hc_home.articles_per_category', 5);

            return $categories->each(function(Category $category) use($categoryLimit, $articleLimit) {
                // limit child category and child category article count
                $category->setRelation('children', $category->children->take($categoryLimit));
                $category->children->each(function(Category $child) use($articleLimit) {
                    $child->setRelation('articles', $child->articles->take($articleLimit));
                });

                // chunk parent category articles into "virtual" child categories
                if ($category->children->isEmpty()) {
                    $category->chunked_articles = $category->articles->values()->chunk($articleLimit)->map->values()->take($categoryLimit);
                }
                $category->unsetRelation('articles');
            });
        });
    }
}
