<?php namespace App\Http\Controllers;

use Auth;
use App\Tag;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Common\Settings\Settings;
use Illuminate\Support\Collection;

class NewTicketCategoriesController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Request $request
     * @param Settings $settings
     */
    public function __construct(Request $request, Settings $settings)
    {
        $this->request = $request;
        $this->settings = $settings;

        $this->middleware('auth');
    }

    /**
     * Get new ticket categories.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', Tag::class);

        $tags = app(Tag::class)
            ->where('type', 'category')
            ->with('categories')
            ->limit(150)
            ->get(['id', 'name', 'display_name', 'type']);

        return $this->success([
            'tags' => $this->filterCategoriesByPurchases($tags),
        ]);
    }

    /**
     * Filter specified tags by current user envato purchases.
     *
     * @param Tag[]|Collection $tags
     * @return Collection
     */
    private function filterCategoriesByPurchases($tags)
    {
        $user = Auth::user();

        $requireCode = $this->settings->get('envato.enable') && $this->settings->get('envato.require_purchase_code');

        if ( ! $requireCode || ($user && $user->isSuperAdmin())) return $tags;

        $names = $user->purchase_codes->pluck('item_name')
            ->map(function($name) { return strtolower($name); });

        return $tags->filter(function($tag) use($names) {
            return $names->contains(strtolower($tag->name));
        })->values();
    }
}
