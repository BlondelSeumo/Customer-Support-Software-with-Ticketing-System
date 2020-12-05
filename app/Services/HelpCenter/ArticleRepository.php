<?php namespace App\Services\HelpCenter;

use App\Tag;
use Common\Settings\Settings;
use DB;
use App\Article;
use App\ArticleFeedback;
use App\Services\TagRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Arr;
use Str;

class ArticleRepository {

    /**
     * @var Article
     */
    private $article;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var ArticleFeedback
     */
    private $feedback;

    /**
     * @param Article $article
     * @param TagRepository $tagRepository
     * @param ArticleFeedback $feedback
     */
    public function __construct(Article $article, TagRepository $tagRepository, ArticleFeedback $feedback)
    {
        $this->article  = $article;
        $this->feedback = $feedback;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Find article with specified ID.
     *
     * @param integer $id
     *
     * @param array $params
     * @return Collection|Model
     */
    public function findOrFail($id, $params)
    {
        return $this->article
            ->with('tags', 'uploads')
            ->withCategories(Arr::get($params, 'categories'))
            ->findOrFail($id);
    }

    /**
     * @param array $params
     * @return Article
     */
    public function create($params)
    {
        $article = $this->article->create([
            'title'       => $params['title'],
            'body'        => $params['body'],
            'slug'        => isset($params['slug']) ? $params['slug'] : null,
            'description' => isset($params['description']) ? $params['description']: null,
            'draft'       => isset($params['draft']) ? $params['draft'] : 0,
            'extra_data'  => isset($params['extra_data']) ? $params['extra_data'] : null,
        ]);

        $article->categories()->attach($params['categories']);

        if (! is_null($params['uploads'])) {
            $article->uploads()->sync($params['uploads']);
        }

        if (isset($params['tags'])) {
            $tags = app(Tag::class)->insertOrRetrieve($params['tags']);
            $article->tags()->sync($tags->pluck('id'));
        }

        return $article;
    }

    /**
     * Update specified article with given params.
     *
     * @throws ModelNotFoundException
     * @param integer $id
     * @param array $params
     *
     * @return Article
     */
    public function update($id, $params)
    {
        $article = $this->article->findOrFail($id);

        $article->fill([
            'title'       => $params['title'],
            'body'        => $params['body'],
            'slug'        => isset($params['slug']) ? $params['slug']: null,
            'description' => isset($params['description']) ? $params['description']: null,
            'draft'       => isset($params['draft']) ? $params['draft'] : 0,
            'position'    => isset($params['position']) ? $params['position'] : 0,
            'extra_data'  => isset($params['extra_data']) ? $params['extra_data'] : null,
        ]);

        $article->save();

        if (isset($params['categories'])) {
            $article->categories()->sync($params['categories']);
        }

        if ( ! is_null($params['uploads'])) {
            $article->uploads()->sync($params['uploads']);
        }

        if (isset($params['tags'])) {
            $tags = app(Tag::class)->insertOrRetrieve($params['tags']);
            $article->tags()->sync($tags->pluck('id'));
        }

        return $article;
    }

    /**
     * Delete specified help center articles.
     *
     * @param integer[] $ids
     * @return int
     */
    public function deleteMultiple($ids)
    {
        //detach categories
        DB::table('category_article')->whereIn('article_id', $ids)->delete();

        //detach tags
        DB::table('taggables')->whereIn('taggable_id', $ids)->where('taggable_type', Article::class)->delete();
        
        //delete articles
        $this->article->whereIn('id', $ids)->delete();

        return count($ids);
    }

    /**
     * Paginate articles matching specified params.
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function paginateArticles($params)
    {
        $paginator = $this->getIndexQuery($params)->paginate(isset($params['perPage']) ? $params['perPage'] : 15);

        $paginator->map(function($article) {
            $article['body'] = Str::limit(strip_tags(html_entity_decode($article['body'])), 200);
            return $article;
        });

        return $paginator;
    }

    /**
     * Submit user feedback about specified article.
     *
     * @param array   $params
     * @return bool
     */
    public function submitFeedback($params)
    {
        //if we are not able to resolve user ip and user is not logged in, bail
        if ( ! $params['user_id'] && ! $params['ip']) return 0;

        $article = $this->article->findOrFail($params['article_id']);

        //if we have user_id, search for existing feedback by user_id
        if ($params['user_id']) {
            $feedback = $article->feedback()->where('user_id', $params['user_id'])->first();
        }

        //if we didn't find feedback by user_id and have client IP, search for existing feedback by client IP
        if ( ! isset($feedback) && $params['ip']) {
            $feedback = $article->feedback()->where('ip', $params['ip'])->first();
        }

        if ( ! $feedback) $feedback = $this->feedback->newInstance();

        return $feedback->fill($params)->save();
    }

    /**
     * Create eloquent query for fetching multiple articles from specified params.
     *
     * @param array $params
     * @return Builder
     */
    private function getIndexQuery($params)
    {
        $query = $this->article->with('categories.parent', 'tags');

        //filter by search query
        if (isset($params['query'])) {
            $search = $params['query'].'%';
            $query->where('title', 'like', $search)
                ->orWhereHas('tags', function(Builder $builder) use($search) {
                    return $builder->where('name', 'like', $search);
                });
        }

        //filter by categories
        if ($categories = Arr::get($params, 'categories')) {
            $query->filterByCategories($categories);
        }

        //filter by tags
        if (isset($params['tags']) && $params['tags']) {
            $query->filterByTags($params['tags']);
        }

        //filter by draft status
        if (Arr::has($params, 'draft')) {
            $query->where('draft', (int) $params['draft']);
        }

        //order
        $defaultOrder = app(Settings::class)->get('articles.default_order', 'position|desc');
        $order = explode('|', Arr::get($params, 'orderBy', $defaultOrder));
        $column = (isset($order[0]) && in_array($order[0], $this->article->orderFields)) ? $order[0] : 'views';
        $direction = isset($order[1]) ? $order[1] : 'desc';

        //order articles by the amount of 'was helpful' user
        //feedback they have in article_feedback table
        if ($order[0] === 'was_helpful') {
            $query->orderByFeedback($direction);
        } else if ($order[0] === 'position') {
            $query->orderByPosition();
        }

        //do a regular order, by a column in main articles table
        else {
            $query->orderBy($column, $direction);
        }

        return $query;
    }
}
