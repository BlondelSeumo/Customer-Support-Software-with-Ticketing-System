<?php

namespace Common\Pages;

use Arr;
use Auth;

class CrupdatePage
{
    /**
     * @param array $data
     * @param CustomPage $page
     * @return CustomPage
     */
    public function execute($page, $data)
    {
        $attributes = [
            'title' => $data['title'],
            'body' => $data['body'],
            'slug' => Arr::get($data, 'slug') ?: Arr::get($data, 'title'),
            'user_id' => Auth::id(),
            'hide_nav' => $data['hide_nav'] ?? false,
        ];

        $page->fill($attributes)->save();

        return $page;
    }
}
