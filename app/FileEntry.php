<?php

namespace App;

use Common\Files\FileEntry as CommonFileEntry;

class FileEntry extends CommonFileEntry
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function replies()
    {
        return $this->morphedByMany(Reply::class, 'model', 'file_entry_models');
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function canned_replies()
    {
        return $this->morphedByMany(CannedReply::class, 'model', 'file_entry_models');
    }
}
