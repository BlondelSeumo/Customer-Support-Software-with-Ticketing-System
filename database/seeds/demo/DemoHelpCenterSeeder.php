<?php

use App\Article;
use App\Category;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;

class DemoHelpCenterSeeder extends Seeder
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Article
     */
    private $article;

    private $demoCategories;

    /**
     * DemoSeeder constructor.
     * @param User $user
     * @param Category $category
     * @param Article $article
     * @param Filesystem $fs
     */
    public function __construct(User $user, Category $category, Article $article, Filesystem $fs)
    {
        $this->fs = $fs;
        $this->user = $user;
        $this->article = $article;
        $this->category = $category;

        $this->demoCategories = json_decode($this->fs->get(database_path('seeds/demo/demo-categories.json')), true);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $parents = $this->seedParentCategories();

        $children = $this->seedChildCategories($parents);

        $articleIds = $this->seedArticles();

        $this->attachArticlesToCategories($children, $parents, $articleIds);
    }

    /**
     * Seed demo parent help center categories.
     *
     * @return Collection
     */
    private function seedParentCategories()
    {
        $this->category->insert(
            collect($this->demoCategories['parents'])->map(function ($name) {
                return ['name' => $name];
            })->toArray()
        );

        return $this->category->whereIn('name', $this->demoCategories['parents'])->get();
    }

    /**
     * Seed demo child help center categories.
     *
     * @param Collection $parents
     * @return Collection
     */
    private function seedChildCategories($parents)
    {
        $children = $parents->map(function (Category $parent) {
            return collect($this->demoCategories['children'])->map(function ($name) use ($parent) {
                return ['name' => $name, 'parent_id' => $parent->id];
            });
        })->flatten(1)->toArray();

        $this->category->insert($children);

        return $this->category->whereIn('name', $this->demoCategories['children'])->get();
    }

    /**
     * Seed demo help center articles.
     *
     * @return Collection
     */
    private function seedArticles()
    {
        $articleNames = collect($this->demoCategories['articles'])->flatten(1);

        $articles = $articleNames->map(function ($name) {
            return ['title' => $name, 'body' => $this->fs->get(database_path('seeds/demo/demo-article-body.txt'))];
        })->toArray();

        $this->article->insert($articles);

        return $this->article->whereIn('title', $articleNames)->get()->pluck('id', 'title');
    }

    /**
     * Attach demo articles to demo child categories.
     *
     * @param Collection $children
     * @param Collection $parents
     * @param Collection $articleIds
     */
    private function attachArticlesToCategories($children, $parents, $articleIds)
    {
        $index = 0; $allPivots = [];

        foreach ($children as $key => $child) {
            $pivots = collect($this->demoCategories['articles'][$index])->map(function ($articles) use ($child, $articleIds) {
                return collect($articles)->map(function ($name) use ($child, $articleIds) {
                    return ['article_id' => $articleIds[$name], 'category_id' => $child->id];
                });
            })->flatten(1)->toArray();

            foreach ($pivots as $pivot) {
                $newPivot = ['article_id' => $pivot['article_id'], 'category_id' => $child->parent_id];
                $exists = Arr::first($allPivots, function($p) use($newPivot) {
                    return $p['article_id'] === $newPivot['article_id'] && $p['category_id'] === $newPivot['category_id'];
                });
                if ($exists) continue;
                $pivots[] = $newPivot;
            }

            $allPivots = array_merge($allPivots, $pivots);

            $index++;

            //reset index to back to zero once it reaches 6
            //because there are only 5 6 article categories
            if ($index === 6) {
                $index = 0;
            }
        }

        DB::table('category_article')->insert($allPivots);
    }
}
