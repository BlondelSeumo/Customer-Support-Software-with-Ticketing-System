<?php

namespace App\Services\HelpCenter;

class AddIdToAllHtmlHeadings
{
    public function execute($html)
    {
        return preg_replace_callback( '/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i', function($matches) {
            $decodedId = html_entity_decode($matches[3]);
            $slugifiedId = slugify(strip_tags($decodedId));
            if ( ! stripos($matches[0], 'id=')) {
                $matches[0] = $matches[1] . $matches[2] . ' id="' . $slugifiedId . '">' . $decodedId . $matches[4];
            } else {
                $matches[0] = preg_replace('/id=".+?"/i', 'id="' . $slugifiedId . '"', $matches[0]);
            }
            return $matches[0];
        }, $html);
    }
}
