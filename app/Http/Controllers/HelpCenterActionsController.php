<?php namespace App\Http\Controllers;

use App\Services\HelpCenter\Actions\ExportHelpCenter;
use App\Services\HelpCenter\Actions\ExportHelpCenterImages;
use App\Services\HelpCenter\Actions\ImportHelpCenter;
use Auth;
use Illuminate\Http\Request;
use Common\Core\BaseController;
use Storage;
use Symfony\Component\HttpFoundation\Response;

class HelpCenterActionsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->middleware('isAdmin');
        $this->request = $request;
    }

    /**
     * Delete help center images that are not attached to any articles.
     */
    public function deleteUnusedImages()
    {
        $names = app(ExportHelpCenterImages::class)->execute();

        $files = Storage::disk('public')->files('article-images');
        $toDelete = array_diff($files, $names->toArray());

        return Storage::disk('public')->delete($toDelete);
    }

    /**
     * Export help center content and images in a .zip file.
     *
     * @return Response
     */
    public function export()
    {
        $filename = app(ExportHelpCenter::class)
            ->execute($this->request->get('format', 'json'));

        return response(file_get_contents($filename), 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="hc-export.zip'
        ]);
    }

    /**
     * Import help center content and images from .zip file.
     *
     * @param Request $request
     */
    public function import(Request $request) {
        $path = $request->file('data')->path();
        app(ImportHelpCenter::class)->execute($path);
    }
}
