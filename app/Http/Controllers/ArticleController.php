<?php namespace App\Http\Controllers;

use App\Article;
use App\Jobs\IncrementArticleViews;
use App\Services\HelpCenter\Actions\GenerateArticleContentNav;
use Illuminate\Http\Request;
use App\Http\Requests\ModifyArticles;
use App\Services\HelpCenter\ArticleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\Core\BaseController;

class ArticleController extends BaseController {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ArticleRepository
     */
    private $repository;

    /**
     * @param Request $request
     * @param ArticleRepository $repository
     */
    public function __construct(Request $request, ArticleRepository $repository)
    {
        $this->request    = $request;
        $this->repository = $repository;
    }

    /**
     * Return all help center articles current user can view.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $this->authorize('index', Article::class);

        $params = $this->request->all();

        $pagination = $this->repository->paginateArticles($params);

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $this->authorize('show', Article::class);

        $article = $this->repository->findOrFail($id, $this->request->all());
        dispatch(new IncrementArticleViews($article->id));

        return $this->success([
            'article' => $article,
            'contentNav' => app(GenerateArticleContentNav::class)->execute($article),
        ]);
    }

    /**
     * Update specified help center article.
     *
     * @param $id
     * @param ModifyArticles $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, ModifyArticles $request)
    {
        $this->authorize('update', Article::class);

        $article = $this->repository->update($id, $this->request->all());

        cache()->forget(HelpCenterController::HC_HOME_CACHE_KEY);

        return $this->success(['data' => $article]);
    }

    /**
     * Create new help center article.
     *
     * @param ModifyArticles $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ModifyArticles $request)
    {
        $this->authorize('store', Article::class);

        $article = $this->repository->create($this->request->all());

        cache()->forget(HelpCenterController::HC_HOME_CACHE_KEY);

        return $this->success(['data' => $article], 201);
    }

    /**
     * Delete specified help center articles.
     *
     * @return JsonResponse
     */
    public function destroy()
    {
        $this->authorize('destroy', Article::class);

        $this->validate($this->request, [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|min:1',
        ]);

        $deleted = $this->repository->deleteMultiple($this->request->get('ids'));

        cache()->forget(HelpCenterController::HC_HOME_CACHE_KEY);

        return $this->success(['data' => $deleted], 202);
    }
}
