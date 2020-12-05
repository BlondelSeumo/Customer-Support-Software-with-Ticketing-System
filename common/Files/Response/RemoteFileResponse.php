<?php

namespace Common\Files\Response;

use Carbon\Carbon;
use Common\Files\FileEntry;

class RemoteFileResponse implements FileResponse
{
    /**
     * @param FileEntry $entry
     * @param array $options
     * @return mixed
     */
    public function make(FileEntry $entry, $options)
    {
        if ($options['disposition'] === 'attachment') {
            return redirect($entry->getDisk()->temporaryUrl(
                $entry->getStoragePath($options['useThumbnail']),
                Carbon::now()->addMinutes(5),
                [
                    'ResponseContentType' => 'application/octet-stream',
                    'ResponseContentDisposition' => "attachment; filename={$entry->name}",
                ]
            ));
        } else {
            return redirect($entry->getDisk()->url($entry->getStoragePath($options['useThumbnail'])));
        }
    }
}
