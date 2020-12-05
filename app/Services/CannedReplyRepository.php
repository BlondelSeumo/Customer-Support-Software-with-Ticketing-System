<?php namespace App\Services;

use Common\Database\Paginator;
use DB;
use App\CannedReply;
use Common\Files\FileEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class CannedReplyRepository {

    /**
     * CannedReply model.
     *
     * @var CannedReply
     */
    private $cannedReply;

    /**
     * Upload model.
     *
     * @var FileEntry
     */
    private $upload;

    /**
     * CannedReplyRepository constructor.
     *
     * @param CannedReply $cannedReply
     * @param FileEntry $fileEntry
     */
    public function __construct(CannedReply $cannedReply, FileEntry $fileEntry)
    {
        $this->upload = $fileEntry;
        $this->cannedReply = $cannedReply;
    }

    /**
     * Find canned reply by specified id.
     *
     * @param int $id
     * @return CannedReply
     */
    public function findOrFail($id)
    {
        return $this->cannedReply->findOrfail($id);
    }

    /**
     * Paginate existing canned replies.
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function paginateCannedReplies($params)
    {
        $paginator = new Paginator($this->cannedReply, $params);
        $paginator->with('uploads');

        if ($userId = Arr::get($params, 'user_id')) {
            $paginator->where('user_id', $userId);
        }

        if ($shared = Arr::get($params, 'shared')) {
            $paginator->query()->orWhere('shared', true);
        }

        return $paginator->paginate();
    }

    /**
     * Create a new canned reply.
     *
     * @param int   $userId
     * @param array $params
     *
     * @return CannedReply
     */
    public function create($userId, $params)
    {
        $cannedReply = $this->cannedReply->create([
            'body'    => $params['body'],
            'name'    => $params['name'],
            'user_id' => $userId
        ]);

        $this->syncUploads($cannedReply, Arr::get($params, 'uploads'));

        return $cannedReply->load('uploads');
    }

    /**
     * Update existing canned reply.
     *
     * @param CannedReply $cannedReply
     * @param array $params
     *
     * @return CannedReply
     */
    public function update(CannedReply $cannedReply, $params)
    {
        $cannedReply->fill(Arr::except($params, 'uploads'))->save();

        $this->syncUploads($cannedReply, Arr::get($params, 'uploads'));

        return $cannedReply->load('uploads');
    }

    /**
     * Delete specified canned replies.
     *
     * @param array $ids
     * @return bool
     */
    public function delete($ids)
    {
        //detach uploads from canned replies
        DB::table('file_entry_models')->where('model_type', CannedReply::class)->whereIn('model_id', $ids)->delete();

        return $this->cannedReply->whereIn('id', $ids)->delete();
    }

    /**
     * Sync canned reply uploads with specified ones.
     *
     * @param CannedReply $cannedReply
     * @param array $uploads
     */
    private function syncUploads(CannedReply $cannedReply, $uploads)
    {
        if ( ! is_null($uploads)) {
            $cannedReply->uploads()->sync($uploads);
        }
    }
}
