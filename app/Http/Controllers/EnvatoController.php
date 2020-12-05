<?php namespace App\Http\Controllers;

use App\Tag;
use App\User;
use Arr;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Envato\EnvatoApiClient;
use Common\Core\BaseController;

class EnvatoController extends BaseController
{
    /**
     * Request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * EnvatoApiClient instance.
     *
     * @var EnvatoApiClient
     */
    private $envatoClient;

    /**
     * Tag model instance.
     *
     * @var Tag
     */
    private $tag;

    /**
     * EnvatoController constructor.
     *
     * @param Request $request
     * @param Tag $tag
     * @param EnvatoApiClient $envatoClient
     */
    public function __construct(Request $request, Tag $tag, EnvatoApiClient $envatoClient)
    {
        $this->tag = $tag;
        $this->request = $request;
        $this->envatoClient = $envatoClient;
    }

    /**
     * Validate specified envato purchase code.
     *
     * @return JsonResponse
     */
    public function validateCode()
    {
        $code = $this->request->get('purchase_code');

        if ($r = $this->envatoClient->purchaseCodeIsValid($code)) {
            return $this->success(['data' => $r]);
        } else {
            return $this->error(__('This purchase code is not valid.'));
        }
    }

    public function addPurchaseUsingCode(): JsonResponse
    {
        $this->validate($this->request, [
            'purchase_code' => 'required|string'
        ]);

        $envatoPurchase = $this->envatoClient->getPurchaseByCode($this->request->get('purchase_code'));
        if ( ! $envatoPurchase) {
            return $this->error(__('Could not find purchase with that code.'));
        }

        $purchase = Auth::user()->updatePurchases([$envatoPurchase], Arr::get($envatoPurchase, 'buyer'))[0];

        return $this->success(['purchase' => $purchase]);
    }

    /**
     * Import user items via envato API.
     *
     * @return \Illuminate\Support\Collection
     */
    public function importItems()
    {
        $names = $this->envatoClient->getItems();

        return collect($names)->map(function($name) {
            $tag = $this->tag->firstOrNew(['name' => $name]);
            $tag->fill(['type' => 'category'])->save();
            return $tag;
        });
    }
}
