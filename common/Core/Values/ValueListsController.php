<?php namespace Common\Core\Values;

use Common\Core\BaseController;
use Common\Localizations\Localization;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValueListsController extends BaseController
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Localization
     */
    private $localization;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Filesystem $fs
     * @param Localization $localization
     * @param Request $request
     */
    public function __construct(Filesystem $fs, Localization $localization, Request $request)
    {
        $this->fs = $fs;
        $this->request = $request;
        $this->localization = $localization;
    }

    /**
     * @param string $names
     * @return JsonResponse
     */
    public function index($names)
    {
        $values = app(ValueLists::class)->get($names, $this->request->all());
        return $this->success($values);
    }
}
