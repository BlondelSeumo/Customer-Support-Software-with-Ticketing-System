<?php namespace App\Http\Controllers;

use DB;
use App;
use Auth;
use Config;
use App\User;
use App\Ticket;
use App\Article;
use Common\Settings\Settings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Common\Core\BaseController;
use Illuminate\Support\Str;

class SearchController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @var Article
     */
    private $article;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @param Request $request
     * @param Settings $settings
     * @param Ticket $ticket
     * @param User $user
     * @param Article $article
     */
    public function __construct(Request $request, Settings $settings, Ticket $ticket, User $user, Article $article)
    {
        $this->user = $user;
        $this->ticket = $ticket;
        $this->article = $article;
        $this->request = $request;
        $this->settings = $settings;
        $this->perPage = $this->request->get('perPage', 5);
    }

    /**
     * @return JsonResponse
     */
    public function all()
    {
        $results = [
            'tickets' => $this->tickets(true),
            'users' => $this->users(true),
            'articles' => $this->articles(true),
        ];

        return $this->success(['results' => $results]);
    }

    /**
     * @param bool $dataOnly
     * @return LengthAwarePaginator|JsonResponse
     */
    public function articles($dataOnly = false)
    {
        $this->authorize('index', Article::class);

        $bodyLimit = $this->request->get('bodyLimit', 200);
        $categories = $this->request->get('categories');

        $ids = $this->article->search($this->request->get('query'))->keys();

        $query = $this->article->whereIn('id', $ids)
            ->where('draft', 0)
            ->withCategories($categories);

        // filter by specified categories
        if ($categories) {
            $query->filterByCategories($categories);
        }

        $query = $this->filterArticlesByEnvato($query);

        // order mysql query by search provider order
        if ($ids->isNotEmpty() && Config::get('database.default') === 'mysql') {
            $ids = $ids->implode(',');
            $query->orderByRaw(DB::raw("field(id,$ids)"), $ids)->get();
        }

        $pagination = $query->paginate($this->perPage);

        $pagination->transform(function(Article $article) use($bodyLimit) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'body' => Str::limit(strip_tags(html_entity_decode($article->body)), $bodyLimit),
                'description' => $article->description,
                'categories' => $article->categories,
            ];
        });

        $options = [
            'prerender' => [
                'view' => "search.index",
                'config' => "search.index",
            ]
        ];

        if ($dataOnly) {
            return $pagination;
        } else {
            return $this->success([
                'pagination' => $pagination,
                'query' => $this->request->get('query'),
            ], 200, $options);
        }


    }

    /**
     * @param bool $dataOnly
     * @return LengthAwarePaginator|JsonResponse
     */
    public function users($dataOnly = false)
    {
        $this->authorize('index', User::class);

        $pagination = $this->user->search($this->request->get('query'))
            ->paginate($this->perPage);

        if ($dataOnly) {
            return $pagination;
        } else {
            return $this->success([
                'pagination' => $pagination
            ]);
        }
    }

    /**
     * @param bool $dataOnly
     * @return LengthAwarePaginator|JsonResponse
     */
    public function tickets($dataOnly = false)
    {
        $this->authorize('index', Ticket::class);

        $detailed = $this->request->get('detailed', false);

        $pagination = $this->ticket->search($this->request->get('query'));
        if ($orderBy = $this->request->get('orderBy')) {
            $pagination->orderBy($this->request->get('orderBy'), $this->request->get('orderDir'));
        }
        $pagination = $pagination->paginate($this->perPage);
        $pagination->load('latest_reply');
        $pagination->each(function(Ticket $ticket) {
            if ($ticket->latest_reply) {
                $ticket->latest_reply->stripBody();
            }
        });

        // load detailed information about ticket, if requested
        if ($detailed) {
            $pagination->load(['user', 'tags']);
            $pagination->loadCount('replies');
        }

        if ($dataOnly) {
            return $pagination;
        } else {
            return $this->success(['pagination' => $pagination]);
        }
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    private function filterArticlesByEnvato(Builder $query)
    {
        // filter by user envato purchases
        if ($this->settings->get('envato.filter_search') && Auth::check() && ! Auth::user()->isSuperAdmin()) {
            return $query->whereHas('categories', function(Builder $q) {
                $names = Auth::user()->purchase_codes->pluck('item_name');
                $q->whereIn('name', $names->toArray());
            });
        }

        return $query;
    }
}
