<?php

namespace App\Http\Controllers;

use App\Article;
use App\FileEntry;
use Common\Core\BaseController;
use Common\Files\Response\DownloadFilesResponse;
use Illuminate\Http\Request;

class ArticleAttachmentsController extends BaseController
{
    /**
     * @var FileEntry
     */
    private $fileEntry;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param FileEntry $fileEntry
     * @param Request $request
     */
    public function __construct(FileEntry $fileEntry, Request $request)
    {
        $this->fileEntry = $fileEntry;
        $this->request = $request;
    }

    public function download(Article $article, $hashes)
    {
        $this->authorize('show', $article);

        $hashes = explode(',', $hashes);
        $fileEntryIds = array_map(function($hash) {
            return $this->fileEntry->decodeHash($hash);
        }, $hashes);

        $fileEntries = $article->uploads()->whereIn('file_entries.id', $fileEntryIds)->get();

        if ($fileEntries->isEmpty()) {
            abort(404);
        }

        return app(DownloadFilesResponse::class)->create($fileEntries);
    }
}
