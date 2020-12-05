<?php

use Cocur\Slugify\Slugify;

if (! function_exists('slugify')) {
    /**
     * @param  string  $title
     * @param  string  $separator
     * @return string
     */
    function slugify($title, $separator = '-')
    {
        $slugified = (new Slugify())->slugify($title, $separator);
        // $slugified = Str::slug($title, $separator);

        if ( ! $slugified) {
            $slugified = strtolower(preg_replace('/[\s_]+/', $separator, $title));
        }

        return $slugified;
    }
}

if (! function_exists('castToBoolean')) {
    /**
     * @param mixed $string
     * @return bool|null|string
     */
    function castToBoolean($string)
    {
        switch ($string) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            default:
                return (string) $string;
        }
    }
}
