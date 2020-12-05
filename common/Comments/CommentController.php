<?php

namespace Common\Comments;

use Common\Core\BaseController;
use Common\Database\Paginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends BaseController
{
    /**
     * @var Comment
     */
    private $comment;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Comment $comment
     * @param Request $request
     */
    public function __construct(Comment $comment, Request $request)
    {
        $this->comment = $comment;
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function index()
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [Comment::class, $userId]);

        $paginator = new Paginator($this->comment, $this->request->all());

        if ($userId = $paginator->param('userId')) {
            $paginator->where('user_id', $userId);
        }

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * @param Comment $comment
     * @return Response
     */
    public function show(Comment $comment)
    {
        $this->authorize('show', $comment);

        return $this->success(['comment' => $comment]);
    }

    /**
     * @param CrupdateCommentRequest $request
     * @return Response
     */
    public function store(CrupdateCommentRequest $request)
    {
        $this->authorize('store', Comment::class);

        $comment = app(CrupdateComment::class)->execute($request->all());

        return $this->success(['comment' => $comment]);
    }

    /**
     * @param Comment $comment
     * @param CrupdateCommentRequest $request
     * @return Response
     */
    public function update(Comment $comment, CrupdateCommentRequest $request)
    {
        $this->authorize('store', $comment);

        $comment = app(CrupdateComment::class)->execute($request->all(), $comment);

        return $this->success(['comment' => $comment]);
    }

    /**
     * @param string $ids
     * @return Response
     */
    public function destroy($ids)
    {
        $commentIds = explode(',', $ids);
        $this->authorize('store', [Comment::class, $commentIds]);

        $this->comment->whereIn('id', $commentIds)->delete();

        return $this->success();
    }
}
