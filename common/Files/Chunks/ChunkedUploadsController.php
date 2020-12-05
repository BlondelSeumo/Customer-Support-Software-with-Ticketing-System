<?php

namespace Common\Files\Chunks;

use Common\Files\Traits\TransformsFileEntryResponse;
use File;
use Common\Core\BaseController;
use Common\Files\FileEntry;
use Common\Settings\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChunkedUploadsController extends BaseController
{
    use HandlesUploadChunks, TransformsFileEntryResponse;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Request $request
     * @param Settings $settings
     */
    public function __construct(Request $request, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    /**
     * @return JsonResponse
     */
    public function load()
    {
        $clientFingerprint = $this->request->get('_fingerprint');
        $totalChunks = (int) $this->request->get('_chunkCount');
        $chunkDir = $this->chunkDir($clientFingerprint);

        $response = ['uploadedChunks' => [], 'fileEntry' => null];

        if (File::exists($chunkDir)) {
            $response['uploadedChunks'] = collect(File::files($chunkDir))
                ->map(function($path) {
                    return ['number' => basename($path), 'size' => filesize($path)];
                })->filter(function($file) {
                    // chunks might be corrupted with 0 byte size sometimes
                    return $file['number'] !== self::$finalFileName && $file['size'] > 0;
                })
                ->map(function($file) {
                    $file['number'] = (int) $file['number'];
                    return $file;
                })->sortBy('number')->values();

            if ($response['uploadedChunks']->count() === $totalChunks) {
                $response['fileEntry'] = app(AssembleFileFromChunks::class)->execute($this->request->except('file'));
            }

            $response = $this->transformFileEntryResponse($response, $this->request->all());
        }

        return $this->success($response);
    }

    /**
     * @return JsonResponse
     */
    public function storeChunk()
    {
        $this->authorize('store', FileEntry::class);

        app(StoreChunkOnDisk::class)->execute(
            $this->request->get('_fingerprint'),
            $this->request->get('_chunkNumber'),
            $this->request->file('file')
        );

        return $this->success();
    }
}
