<?php

namespace Common\Files\Chunks;

use File;
use Illuminate\Http\UploadedFile;

class StoreChunkOnDisk
{
    use HandlesUploadChunks;

    /**
     * @param string $clientFingerprint
     * @param string $chunkNum
     * @param UploadedFile $chunkFile
     */
    public function execute($clientFingerprint, $chunkNum, $chunkFile)
    {
        $chunkDir = $this->chunkDir($clientFingerprint);

        if ( ! File::exists($chunkDir)) {
            @File::makeDirectory($chunkDir);
        }

        $chunkPath = "$chunkDir/$chunkNum";

        $stream = fopen($chunkPath, 'w+b');
        $resource = fopen($chunkFile->getRealPath(), 'r+');
        stream_copy_to_stream($resource, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        if (is_resource($resource)) {
            fclose($resource);
        }
    }
}
