<?php

namespace App\Services\HelpCenter\Actions;

use App\Article;
use App\Category;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Str;
use Storage;
use Symfony\Component\DomCrawler\Crawler;
use ZipArchive;

class ExportHelpCenterImages
{
    /**
     * Export help center images into a specified .zip file.
     *
     * @param ZipArchive|null $zip
     * @param Article $article
     * @param string $prefix
     * @return Collection
     */
    public function execute(ZipArchive $zip = null, Article $article = null, $prefix = null)
    {
        $names = $this->getAllHelpCenterImages($article);

        if ($zip) {
            $names->each(function($name) use ($zip, $prefix) {
                try {
                    $contents = Storage::disk('public')->get($name);
                    $path = $prefix ? "$prefix/images/$name" : "images/$name";
                    $zip->addFromString($path, $contents);
                } catch (FileNotFoundException $e) {
                    //
                }
            });
        }

        return $names;
    }

    /**
     * Get all non external help center article images.
     *
     * @param Article $article
     * @return Collection
     */
    private function getAllHelpCenterImages(Article $article = null)
    {
        $host = parse_url(config('app.url'))['host'];
        $names = collect();

        if ($article) {
            $articles = collect([$article]);
        } else {
            $articles = app(Article::class)->get();
        }

        $articles->each(function (Article $article) use ($host, $names) {
            $crawler = new Crawler();
            $crawler->addHtmlContent($article->body);

            $crawler->filter('img')->each(function (Crawler $node) use ($host, $names) {
                if (Str::contains($node->attr('src'), $host) || Str::startsWith($node->attr('src'), 'storage')) {
                    $names->push(explode('storage/', $node->attr('src'))[1]);
                }
            });
        });

        // get category images also
        $categoryImages = app(Category::class)
            ->whereNotNull('image')
            ->pluck('image')
            ->map(function($path) {
                return str_replace('storage/', '', $path);
            });
        $names = $names->concat($categoryImages);

        return $names;
    }
}
