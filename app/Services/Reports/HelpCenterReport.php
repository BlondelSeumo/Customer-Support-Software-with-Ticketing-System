<?php

namespace App\Services\Reports;

use DB;
use Cache;
use App\Article;
use Carbon\Carbon;
use App\SearchTerm;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class HelpCenterReport
{
    /**
     * @var SearchTerm
     */
    private $searchTerm;

    /**
     * @var Article
     */
    private $article;

    /**
     * @param SearchTerm $searchTerm
     * @param Article $article
     */
    public function __construct(SearchTerm $searchTerm, Article $article)
    {
        $this->searchTerm = $searchTerm;
        $this->article = $article;
    }

    /**
     * @return array
     */
    public function generate()
    {
        return Cache::remember('reports.helpCenter', Carbon::now()->addHours(12), function() {
            return [
                'failed_searches' => $this->generateSearchReport(['result_count' => 0]),
                'popular_searches' => $this->generateSearchReport([['result_count', '>', 0]]),
                'popular_articles' => $this->generatePopularArticles(),
            ];
        });
    }

    /**
     * @param array $wheres
     * @param string $orderCol
     * @param string $orderDir
     * @return Collection
     */
    public function generateSearchReport($wheres, $orderCol = 'count', $orderDir = 'desc')
    {
        $terms = $this->searchTerm
            ->where($wheres)
            ->select(
                'id',
                'term',
                DB::raw('max(created_at) as last_seen'),
                DB::raw('count(*) as count'),
                DB::raw('group_concat(category_id) as category_id'),
                DB::raw('SUM(CASE WHEN created_ticket THEN 1 ELSE 0 END) AS resulted_in_ticket'),
                DB::raw('SUM(CASE WHEN clicked_article THEN 1 ELSE 0 END) AS clicked_article')
            )
            ->groupBy('normalized_term')
            ->orderBy($orderCol, $orderDir)
            ->limit(30)
            ->get();

        $terms->transform(function($term) {
            $term->category_id = explode(',', $term->category_id);
            $term->category_id = array_key_first(array_count_values($term->category_id));
            $term->resulted_in_ticket = (int) $term->resulted_in_ticket;
            return $term;
        });

        $terms->load(['category' => function(BelongsTo $query) {
            return $query->select('id', 'name', 'image');
        }]);

        return $terms;
    }

    private function generatePopularArticles()
    {
        $prefix = DB::getTablePrefix();
        $positive = "(SELECT count(*) FROM {$prefix}article_feedback WHERE was_helpful = 1 AND article_id = {$prefix}articles.id) as positive_votes";
        $negative = "(SELECT count(*) FROM {$prefix}article_feedback WHERE was_helpful = 0 AND article_id = {$prefix}articles.id) as negative_votes";

        return $this->article
            ->orderBy('views', 'desc')
            ->select(['id', 'views', 'slug', 'title', DB::raw($positive), DB::raw($negative)])
            ->with(['categories' => function(BelongsToMany $query) {
                return $query->whereNull('parent_id')->select(['name', 'id', 'image']);
            }])
            ->get()
            ->transform(function(Article $article) {
                $totalLikes = $article->positive_votes + $article->negative_votes;
                $article->score = $totalLikes ? round(($article->positive_votes / $totalLikes) * 100) : null;
                return $article;
            });
    }

}
