<?php

namespace App\Services\HelpCenter\Actions;

use App\Article;
use Arr;

class GenerateArticleContentNav
{
    public function execute(Article $article)
    {
        $nav = [];

        preg_match_all('/<(h[2|3]) id=".*?">(.*?)<\/h[2|3]>/', $article->body, $matches);

        foreach ($matches[2] as $key => $heading) {
            $type = $matches[1][$key];
            if (str_contains($heading, '<a')) {
                preg_match('/<a href=".*?">(.*?)<\/a>/', $heading, $aMatches);
                $heading = $aMatches[1];
            }
            $precededByH2 = Arr::first($nav, function($navItem) {
                return $navItem['type'] === 'h2';
            });
            $indent = $type === 'h3' && $precededByH2;
            $displayName = strip_tags($heading);
            $nav[] = ['display_name' => $displayName, 'slug' => slugify($displayName), 'indent' => $indent, 'type' => $type];
        }

        return $nav;
    }

}
