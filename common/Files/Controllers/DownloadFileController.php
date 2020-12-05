<?php namespace Common\Files\Controllers;

use Common\Core\BaseController;
use Common\Files\FileEntry;
use Common\Files\Response\DownloadFilesResponse;
use Common\Files\Response\FileResponseFactory;
use Illuminate\Http\Request;

class DownloadFileController extends BaseController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var FileEntry
     */
    protected $fileEntry;

    /**
     * @var FileResponseFactory
     */
    protected $fileResponseFactory;

    /**
     * @param Request $request
     * @param FileEntry $fileEntry
     * @param FileResponseFactory $fileResponseFactory
     */
    public function __construct(Request $request, FileEntry $fileEntry, FileResponseFactory $fileResponseFactory)
    {
        $this->request = $request;
        $this->fileEntry = $fileEntry;
        $this->fileResponseFactory = $fileResponseFactory;
    }

    public function download()
    {
        $hashes = explode(',', $this->request->get('hashes'));
        $ids = array_map(function($hash) {
            return $this->fileEntry->decodeHash($hash);
        }, $hashes);

        $entries = $this->fileEntry->whereIn('id', $ids)->get();

        // TODO: refactor file entry policy to accept multiple IDs
        $entries->each(function($entry) {
            $this->authorize('show', [FileEntry::class, $entry]);
        });

        return app(DownloadFilesResponse::class)->create($entries);
    }
}
