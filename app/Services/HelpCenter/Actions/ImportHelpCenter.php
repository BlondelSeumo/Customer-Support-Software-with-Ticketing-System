<?php

namespace App\Services\HelpCenter\Actions;

use App\Article;
use App\Category;
use App\Tag;
use Cache;
use Common\Files\FileEntry;
use DB;
use Exception;
use Arr;
use Str;
use Storage;
use ZipArchive;

class ImportHelpCenter
{
    /**
     * @var Category
     */
    private $category;

    /**
     * @var Article
     */
    private $article;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @param Category $category
     * @param Article $article
     * @param Tag $tag
     */
    public function __construct(Category $category, Article $article, Tag $tag)
    {
        $this->category = $category;
        $this->article = $article;
        $this->tag = $tag;
    }

    /**
     * Import help center content and images from .zip file.
     *
     * @param string $path
     */
    public function execute($path) {
        $zip = new ZipArchive();
        $zip->open($path);

        // store help center images
        for ($i = 0; $i < $zip->numFiles; $i++){
            $stat = $zip->statIndex( $i );
            if (Str::startsWith(dirname($stat['name']), 'images/')) {
                list($root, $folder, $name) = explode('/', $stat['name']);
                Storage::disk('public')->put("$folder/$name", $zip->getFromIndex($stat['index']));
            }
        }

        $helpCenter = json_decode($zip->getFromName('help-center.json'), true);
        $oldBaseUri = $zip->getFromName('base-url.txt');
        $zip->close();

        // truncate old help center data
        DB::table('categories')->truncate();
        DB::table('articles')->truncate();
        DB::table('category_article')->truncate();

        // unguard models and clear old relations
        Category::unguard(); Article::unguard(); Tag::unguard(); FileEntry::unguard();
        DB::table('taggables')->where('taggable_type', Article::class)->delete();

        collect($helpCenter)->each(function($parentData) use($oldBaseUri) {

            //create parent categories
            $parent = $this->category->create(Arr::except($parentData, ['children', 'id']));

            //create child categories
            collect($parentData['children'])->each(function($childData) use($parent, $oldBaseUri) {
                $childData['parent_id'] = $parent->id;
                $child = $this->category->create(Arr::except($childData, ['articles', 'id']));

                //create articles
                collect($childData['articles'])->each(function($articleData) use($child, $parent, $oldBaseUri) {
                    $articleData['body'] = app(ReplaceArticleImageSrc::class)->execute($articleData['body'], $oldBaseUri);

                    $article = $this->article->firstOrCreate(Arr::except($articleData, ['tags', 'id']));
                    $article->categories()->attach([$child->id, $parent->id]);

                    //create tags
                    collect($articleData['tags'])->each(function($tagData) use($article) {
                        $attrs = ['name' => $tagData['name'], 'type' => $tagData['type'], 'display_name' => $tagData['display_name']];
                        $tag = $this->tag->where('name', $tagData['name'])->first();
                        if ( ! $tag) $tag = $this->tag->create($attrs);

                        try {
                            $article->tags()->attach($tag->id);
                        } catch(Exception $e) {}
                    });
                });
            });
        });

        Cache::flush();
    }
}
