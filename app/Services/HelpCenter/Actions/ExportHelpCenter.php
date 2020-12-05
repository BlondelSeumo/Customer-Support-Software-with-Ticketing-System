<?php

namespace App\Services\HelpCenter\Actions;

use App\Article;
use App\Category;
use File;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use ZipArchive;

class ExportHelpCenter
{
    /**
     * @var ZipArchive
     */
    public $zip;

    public function execute($format)
    {
        $filename = storage_path('app/hc-export.zip');
        @unlink($filename);

        $this->zip = new ZipArchive();
        if ($this->zip->open($filename, ZipArchive::CREATE) !== TRUE) return null;

        // get all hc categories
        $categories = app(Category::class)
            ->rootOnly()
            ->with('children.articles.tags')
            ->get();

        if ($format === 'html') {
            $this->exportAsHtml($categories);
        } else {
            app(ExportHelpCenterImages::class)->execute($this->zip);
            $this->zip->addFromString('base-url.txt', config('app.url'));
            $this->zip->addFromString('help-center.json', json_encode($categories));
        }

        $this->zip->close();

        return $filename;
    }

    private function exportAsHtml(Collection $categories)
    {
        $categories->each(function(Category $parentCategory) {
            $prefix = slugify($parentCategory->name);

            // styles
            $styles = File::files(resource_path('views/help-center/styles'));
            foreach ($styles as $fileInfo) {
                $baseName = $fileInfo->getBasename();
                $this->zip->addFromString("$prefix/assets/styles/$baseName", $fileInfo->getContents());
            }

            // scripts
            $this->zip->addFromString(
                "$prefix/assets/docs-scripts.js",
                app(Filesystem::class)->get(resource_path('views/help-center/docs-scripts.js'))
            );

            $parentCategory->children->each(function(Category $childCategory, $childCatIndex) use($parentCategory, $prefix) {
                $childCategory->articles->each(function(Article $article, $articleIndex) use($parentCategory, $childCategory, $prefix, $childCatIndex) {

                    // images
                    app(ExportHelpCenterImages::class)->execute($this->zip, $article, "$prefix/assets");

                    // replace image urls with relative local path
                    $article->body = app(ReplaceArticleImageSrc::class)->execute(
                        $article->body,
                        'storage',
                        '../../assets/images'
                    );

                    // render .blade article template into string
                    $renderedArticle = view('help-center.article')->with([
                        'category' => $parentCategory,
                        'article' => $article
                    ])->render();

                    $articleName = slugify($article->title);
                    $categoryName = slugify($childCategory->name);
                    $articlePath = "articles/$categoryName/$articleName.html";
                    $this->zip->addFromString("$prefix/$articlePath", $renderedArticle);

                    // create index.html with redirect to first article
                    if ($childCatIndex === 0 && $articleIndex === 0) {
                        $html = view('help-center.docs-index')->with('url', $articlePath);
                        $this->zip->addFromString("$prefix/index.html", $html);
                    }
                });
            });
        });
    }
}
