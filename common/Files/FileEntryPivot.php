<?php

namespace Common\Files;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class FileEntryPivot extends MorphPivot
{
    protected $table = 'file_entry_models';

    protected $casts = ['owner' => 'boolean'];

    /**
     * @param $value
     * @return array
     */
    public function getPermissionsAttribute($value)
    {
        if ( ! $value) return [];

        if (is_string($value)) {
            return json_decode($value, true);
        }

        return $value;
    }
}
