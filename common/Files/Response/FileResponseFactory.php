<?php namespace Common\Files\Response;

use Common\Files\FileEntry;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Request;

class FileResponseFactory
{
    /**
     * @param FileEntry $entry
     * @param string $disposition
     * @return mixed
     */
    public function create(FileEntry $entry, $disposition = 'inline')
    {
        $options = [
            'useThumbnail' => Request::get('thumbnail') && $entry->thumbnail,
            'disposition' => $disposition
        ];

        return $this->resolveResponseClass($entry, $disposition)
            ->make($entry, $options);
    }

    /**
     * @param FileEntry $entry
     * @param string $disposition
     * @return FileResponse
     */
    private function resolveResponseClass(FileEntry $entry, $disposition = 'inline')
    {
        $isLocalDrive = $entry->getDisk()->getAdapter() instanceof Local;
        $staticFileDelivery = config('common.site.static_file_delivery');

        if ($this->shouldRedirectToRemoteUrl($entry)) {
            return new RemoteFileResponse;
        } else if ($isLocalDrive && !$entry->public && $staticFileDelivery) {
            return $staticFileDelivery === 'xsendfile' ?
                new XSendFileResponse :
                new XAccelRedirectFileResponse;
        } elseif ($disposition === 'inline' && $this->shouldReturnRangeResponse($entry)) {
            return new RangeFileResponse;
        } else {
            return new StreamedFileResponse;
        }
    }

    /**
     * @param FileEntry $entry
     * @return bool
     */
    private function shouldReturnRangeResponse(FileEntry $entry)
    {
        return $entry->type === 'video' || $entry->type === 'audio' || $entry->mime === 'application/ogg';
    }

    private function shouldRedirectToRemoteUrl(FileEntry $entry)
    {
        $adapter = $entry->getDisk()->getAdapter();
        $remoteSupportsUrl = $adapter instanceof AwsS3Adapter;
        return config('common.site.remote_file_visibility') === 'public' && $remoteSupportsUrl;
    }
}
