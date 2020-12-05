<?php namespace App\Http\Controllers;

use App\Article;
use Illuminate\Http\Request;
use App\Services\HelpCenter\ArticleRepository;
use Common\Core\BaseController;

class ArticleFeedbackController extends BaseController
{
    /**
     * Laravel request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * ArticleRepository instance.
     *
     * @var ArticleRepository
     */
    private $repository;

    /**
     * ArticleFeedbackController constructor.
     *
     * @param Request $request
     * @param ArticleRepository $repository
     */
    public function __construct(Request $request, ArticleRepository $repository)
    {
        $this->request   = $request;
        $this->repository = $repository;
    }

    /**
     * Submit user feedback about specified article.
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit($id)
    {
        $this->authorize('show', Article::class);

        $this->validate($this->request, [
            'was_helpful' => 'required|boolean',
            'comment'     => 'string|min:1|max:1000',
        ]);

        $userId = $this->request->user() ? $this->request->user()->id : null;

        $this->repository->submitFeedback([
            'article_id'  => $id,
            'comment'     => $this->request->get('comment'),
            'was_helpful' => $this->request->get('was_helpful'),
            'ip'          => $this->request->ip(),
            'user_id'     => $userId,
        ]);

        return $this->success();
    }
}
