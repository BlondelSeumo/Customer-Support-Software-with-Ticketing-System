<?php namespace App\Http\Controllers;

use App\Services\Ticketing\ReplyRepository;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;

class DraftUploadsController extends BaseController {

    /**
     * @var ReplyRepository
     */
    private $replyRepository;

    /**
     * @param ReplyRepository $replyRepository
     */
    public function __construct(ReplyRepository $replyRepository)
    {
        $this->replyRepository = $replyRepository;
    }

    /**
     * @param integer $replyId
     * @param integer $uploadId
     *
     * @return JsonResponse
     */
    public function detach($replyId, $uploadId)
    {
        $draft = $this->replyRepository->findOrFail($replyId);

        $this->authorize('destroy', [$draft, 'drafts']);

        $count = $this->replyRepository->detachUploads($draft, [$uploadId]);

        return $this->success(['data' => $count]);
    }
}
