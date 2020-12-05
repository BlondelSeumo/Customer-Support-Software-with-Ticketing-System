<?php

namespace App\Services\HelpCenter\Actions;

use Symfony\Component\DomCrawler\Crawler;

class ReplaceArticleImageSrc
{
    /**
     * @param string $body
     * @param string $oldBaseUri
     * @param string|null $newBaseUri
     * @return string
     */
    public function execute($body, $oldBaseUri, $newBaseUri = null)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent($body);
        $newBaseUri = $newBaseUri ?: config('app.url');

        $crawler->filter('img')->each(function (Crawler $node) use($oldBaseUri, $newBaseUri) {
            $oldSrc  = $node->attr('src');
            $needles = [$oldBaseUri, 'http://localhost:4200'];

            if (\Str::contains($oldSrc, $needles)) {
                $newSrc = str_replace($needles, $newBaseUri, $oldSrc);
                $node->getNode(0)->setAttribute('src', $newSrc);
            }
        });

        return $crawler->filter('body')->html();
    }
}
