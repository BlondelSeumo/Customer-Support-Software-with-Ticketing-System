<?php

namespace Common\Admin\Appearance\Themes;

use Common\Core\BaseController;
use Common\Database\Paginator;
use Illuminate\Http\Request;
use Common\Admin\Appearance\Themes\CssTheme;
use Illuminate\Http\Response;
use Common\Admin\Appearance\Themes\CrupdateCssThemeRequest;
use Common\Admin\Appearance\Themes\CrupdateCssTheme;

class CssThemeController extends BaseController
{
    /**
     * @var CssTheme
     */
    private $cssTheme;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param CssTheme $cssTheme
     * @param Request $request
     */
    public function __construct(CssTheme $cssTheme, Request $request)
    {
        $this->cssTheme = $cssTheme;
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function index()
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [CssTheme::class, $userId]);

        $paginator = new Paginator($this->cssTheme, $this->request->all());

        if ($userId = $paginator->param('userId')) {
            $paginator->where('user_id', $userId);
        }

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * @param CssTheme $cssTheme
     * @return Response
     */
    public function show(CssTheme $cssTheme)
    {
        $this->authorize('show', $cssTheme);

        return $this->success(['theme' => $cssTheme]);
    }

    /**
     * @param CrupdateCssThemeRequest $request
     * @return Response
     */
    public function store(CrupdateCssThemeRequest $request)
    {
        $this->authorize('store', CssTheme::class);

        $cssTheme = app(CrupdateCssTheme::class)->execute($request->all());

        return $this->success(['theme' => $cssTheme]);
    }

    /**
     * @param CssTheme $cssTheme
     * @param CrupdateCssThemeRequest $request
     * @return Response
     */
    public function update(CssTheme $cssTheme, CrupdateCssThemeRequest $request)
    {
        $this->authorize('store', $cssTheme);

        $cssTheme = app(CrupdateCssTheme::class)->execute($request->all(), $cssTheme);

        return $this->success(['theme' => $cssTheme]);
    }

    /**
     * @param CssTheme $cssTheme
     * @return Response
     */
    public function destroy(CssTheme $cssTheme)
    {
        $this->authorize('destroy', $cssTheme);

        $cssTheme->delete();

        return $this->success();
    }
}
