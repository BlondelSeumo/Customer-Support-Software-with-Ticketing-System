<?php

namespace Common\Domains;

use Auth;
use Common\Core\AppUrl;
use Common\Core\BaseController;
use Common\Core\HttpClient;
use Common\Database\Paginator;
use Common\Domains\Actions\DeleteCustomDomains;
use Common\Settings\Settings;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;


class CustomDomainController extends BaseController
{
    const VALIDATE_CUSTOM_DOMAIN_PATH = 'secure/custom-domain/validate/2BrM45vvfS';

    /**
     * @var CustomDomain
     */
    private $customDomain;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param CustomDomain $customDomain
     * @param Request $request
     */
    public function __construct(CustomDomain $customDomain, Request $request)
    {
        $this->customDomain = $customDomain;
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function index()
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [get_class($this->customDomain), $userId]);

        $paginator = new Paginator($this->customDomain, $this->request->all());
        $paginator->searchColumn = 'host';

        if ($userId) {
            $paginator->where('user_id', $userId);
        } else {
            $paginator->with('user');
        }

        return $this->success(['pagination' => $paginator->paginate()]);
    }

    /**
     * @return Response
     */
    public function store()
    {
        $this->authorize('store', get_class($this->customDomain));

        $this->validate($this->request, [
            'host' => 'required|string|max:100|unique:custom_domains',
            'global' => 'boolean',
        ]);

        $domain = $this->customDomain->create([
            'host' => $this->request->get('host'),
            'user_id' => Auth::id(),
            'global' => $this->request->get('global', false),
        ]);

        return $this->success(['domain' => $domain]);
    }

    /**
     * @param CustomDomain $customDomain
     * @return Response
     */
    public function update(CustomDomain $customDomain)
    {
        $this->authorize('store', $customDomain);

        $this->validate($this->request, [
            'host' => ['string', 'max:100', Rule::unique('custom_domains')->ignore($customDomain->id)],
            'global' => 'boolean',
            'resource_id' => 'integer',
            'resource_type' => 'string',
        ]);

        $data = $this->request->all();
        $data['user_id'] = Auth::id();
        $data['global'] = $this->request->get('global', $customDomain->global);
        $domain = $customDomain->update($data);

        return $this->success(['domain' => $domain]);
    }

    /**
     * @param string $ids
     * @return Response
     */
    public function destroy($ids)
    {
        $domainIds = explode(',', $ids);
        $this->authorize('destroy', [get_class($this->customDomain), $domainIds]);

        app(DeleteCustomDomains::class)->execute($domainIds);

        return $this->success();
    }

    public function authorizeCrupdate()
    {
        $this->authorize('store', get_class($this->customDomain));

        $domainId = $this->request->get('domainId');

        // don't allow attaching current site url as custom domain
        if (app(AppUrl::class)->requestHostMatches($this->request->get('host'))) {
            return $this->error('', ['host' => 'This domain is not valid.']);
        }

        $this->validate($this->request, [
            'host' => ['required', 'string', 'max:100', Rule::unique('custom_domains')->ignore($domainId)],
        ]);

        return $this->success([
            'serverIp' => env('SERVER_IP') ?: $_SERVER['SERVER_ADDR']
        ]);
    }

    /**
     * Proxy method for validation on frontend to avoid cross-domain issues.
     *
     * @return array|JsonResponse|string
     */
    public function validateDomainApi()
    {
        $this->validate($this->request, [
            'host' => 'required|string',
        ]);
        $http = new HttpClient(['verify' => false]);
        $host = trim($this->request->get('host'), '/');
        try {
            return $http->get("$host/" . self::VALIDATE_CUSTOM_DOMAIN_PATH);
        } catch (RequestException $e) {
            return $this->error(__('Could not validate domain.'));
        }
    }

    /**
     * Method for validating if CNAME for custom domain was attached properly.
     * @return Response
     */
    public function validateDomain()
    {
        return $this->success(['result' => 'connected']);
    }
}
