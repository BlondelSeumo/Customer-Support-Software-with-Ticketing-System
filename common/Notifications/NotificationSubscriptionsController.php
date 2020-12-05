<?php

namespace Common\Notifications;

use Common\Core\BaseController;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Arr;
use Ramsey\Uuid\Uuid;

class NotificationSubscriptionsController extends BaseController
{
    /**
     * @var NotificationSubscription
     */
    private $subscription;
    /**
     * @var Filesystem
     */
    private $fs;
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Filesystem $fs
     * @param NotificationSubscription $subscription
     * @param Request $request
     */
    public function __construct(
        Filesystem $fs,
        NotificationSubscription $subscription,
        Request $request
    ) {
        $this->fs = $fs;
        $this->subscription = $subscription;
        $this->request = $request;
    }

    /**
     * @param int $userId
     * @return Response
     */
    public function index($userId)
    {
        $this->authorize('index', [NotificationSubscription::class, $userId]);

        $response = $this->fs->getRequire(resource_path('defaults/notification-settings.php'));
        $subs = $this->subscription->where('user_id', $userId)->get();

        $response['selections'] = collect($response['available_channels'])->mapWithKeys(function($channelName) use($subs) {
            $notifIds = $subs->filter(function(NotificationSubscription $sub) use($channelName) {
                return $sub->channels[$channelName];
            })->pluck('notif_id');
            return [$channelName => $notifIds];
        })->toArray();

        $response['all_notif_ids'] = Arr::flatten(
            Arr::pluck($response['subscriptions'], 'subscriptions.*.notif_id')
        );

        return $this->success($response);
    }

    /**
     * @param int $userId
     * @return Response
     */
    public function update($userId)
    {
        $this->authorize('update', [NotificationSubscription::class, $userId]);
        $selections = collect($this->request->get('selections'));

        // map [email => [01, 02]] to [01 => [email => true], 02 => [email => true]
        $subscriptions = $selections->flatten(1)->unique()->map(function($notifId) use ($userId, $selections) {
            $channels = $selections->keys()->mapWithKeys(function($channelName) use($selections, $notifId) {
                return [$channelName => array_search($notifId, $selections[$channelName]) !== false];
            });
            return ['id' => Uuid::uuid4(), 'notif_id' => $notifId, 'user_id' => $userId, 'channels' => $channels];
        });

        $this->subscription->where('user_id', $userId)->delete();
        $this->subscription->insert($subscriptions->toArray());

        return $this->success();
    }
}
