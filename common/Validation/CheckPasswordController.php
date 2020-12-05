<?php namespace Common\Validation;

use Common\Core\BaseController;
use Common\Settings\Settings;
use DB;
use Hash;
use Illuminate\Http\Request;

class CheckPasswordController extends BaseController
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
    }

    public function check()
    {
        $this->validate($this->request, [
            'table' => 'required|string',
            'id' => 'required|integer',
            'password' => 'required|string',
        ]);

        $record = DB::table($this->request->get('table'))
            ->find($this->request->get('id'));
        $matches = Hash::check($this->request->get('password'), $record->password);

        return $this->success(['matches' => $matches]);
    }
}
