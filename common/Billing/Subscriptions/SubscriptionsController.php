<?php namespace Common\Billing\Subscriptions;

use Closure;
use Common\Billing\BillingPlan;
use Common\Billing\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Common\Core\BaseController;
use Common\Database\Paginator;

class SubscriptionsController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var BillingPlan
     */
    private $billingPlan;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @param Request $request
     * @param BillingPlan $billingPlan
     * @param Subscription $subscription
     */
    public function __construct(
        Request $request,
        BillingPlan $billingPlan,
        Subscription $subscription
    )
    {
        $this->request = $request;
        $this->billingPlan = $billingPlan;
        $this->subscription = $subscription;

        $this->middleware('auth');
    }

    /**
     * Paginate all existing subscriptions.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', Subscription::class);

        $paginator = (new Paginator($this->subscription, $this->request->all()))->with('user');
        $paginator->filterColumns = ['gateway', 'cancelled'];

        $paginator->searchCallback = function(Builder $query, $searchTerm) {
            $query->whereHas('user', function(Builder $query) use($searchTerm) {
                $query->where('email', 'like', "$searchTerm%");
            })->orWhere('gateway', 'like', "$searchTerm%");
        };

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * Create a new subscription.
     *
     * @return JsonResponse
     */
    public function store()
    {
        $this->authorize('update', Subscription::class);

        $this->validate($this->request, [
            'user_id' => 'required|exists:users,id|unique:subscriptions',
            'renews_at' => 'required_without:ends_at|date|nullable',
            'ends_at' => 'required_without:renews_at|date|nullable',
            'plan_id' => 'required|integer|exists:billing_plans,id',
            'description' => 'string|nullable',
        ]);

        $subscription = $this->subscription->create($this->request->all());

        return $this->success(['subscription' => $subscription]);
    }

    /**
     * Update existing subscription.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function update($id)
    {
        $this->authorize('update', Subscription::class);

        $this->validate($this->request, [
            'user_id' => 'exists:users,id|unique:subscriptions',
            'renews_at' => 'date|nullable',
            'ends_at' => 'date|nullable',
            'plan_id' => 'integer|exists:billing_plans,id',
            'description' => 'string|nullable'
        ]);

        $subscription = $this->subscription->findOrFail($id);

        $subscription->fill($this->request->all())->save();

        return $this->success(['subscription' => $subscription]);
    }

    /**
     * Change plan of specified subscription.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function changePlan($id)
    {
        $this->validate($this->request, [
            'newPlanId' => 'required|integer|exists:billing_plans,id'
        ]);

        /** @var Subscription $subscription */
        $subscription = $this->subscription->findOrFail($id);
        $plan = $this->billingPlan->findOrfail($this->request->get('newPlanId'));

        $subscription->changePlan($plan);

        $user = $subscription->user()->first();
        return $this->success(['user' => $user->load('subscriptions.plan')]);
    }

    /**
     * Cancel specified subscription.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel($id)
    {
        $this->validate($this->request, [
            'delete' => 'boolean'
        ]);

        /** @var Subscription $subscription */
        $subscription = $this->subscription->findOrFail($id);
        $user = $subscription->user()->first();

        if ($this->request->get('delete')) {
            $subscription->cancelAndDelete();
            $user->update([
                'card_last_four' => null,
                'card_brand' => null,
                'stripe_id' => null,
            ]);
        } else {
            $subscription->cancel();
        }

        return $this->success(['user' => $user->load('subscriptions.plan')]);
    }

    /**
     * Resume specified subscription.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function resume($id)
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscription->with('plan')->findOrFail($id);
        $subscription->resume();

        return $this->success(['subscription' => $subscription]);
    }
}
