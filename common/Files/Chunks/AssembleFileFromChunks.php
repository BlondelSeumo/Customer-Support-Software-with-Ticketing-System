<?php

namespace Common\Files\Chunks;

use Common\Files\Actions\UploadFile;
use File;
use finfo;
use Illuminate\Http\UploadedFile;
use Arr;
use Str;

class AssembleFileFromChunks
{
    use HandlesUploadChunks;

    public function execute($params)
    {
        $originalName = $params['_originalFileName'];
        $chunkDir = $this->chunkDir($params['_fingerprint']);
        $finalFilePath = "$chunkDir/" . self::$finalFileName;

        if (File::exists($finalFilePath)) {
            unlink($finalFilePath);
        }

        $chunks = collect(File::files($chunkDir))
            ->filter(function($path) {
                return !Str::endsWith($path, self::$finalFileName);
            })
            ->map(function($path) {
                return ['path' => $path, 'number' => (int) basename($path)];
            })->sortBy('number');

        File::put($finalFilePath, '');
        $destination = fopen($finalFilePath, 'ab');
        foreach ($chunks as $chunk) {
            $in = fopen($chunk['path'], 'rb');
            while ($buff = fread($in, 4096)) {
                fwrite($destination, $buff);
            }
            fclose($in);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $uploadedFile = new UploadedFile(
            $finalFilePath,
            $originalName,
            $finfo->file($finalFilePath),
            filesize($finalFilePath),
            0,
            false
        );
        $fileEntry = app(UploadFile::class)
            ->execute(Arr::get($params, 'disk', 'private'), $uploadedFile, $params);
        File::deleteDirectory($chunkDir);

        return $fileEntry;
    }
}
