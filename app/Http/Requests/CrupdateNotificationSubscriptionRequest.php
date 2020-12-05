<?php

namespace App\Http\Requests;

use Auth;
use Common\Core\BaseFormRequest;
use Illuminate\Validation\Rule;

class CrupdateNotificationSubscriptionRequest extends BaseFormRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        $required = $this->getMethod() === 'POST' ? 'required' : '';
        $ignore = $this->getMethod() === 'PUT' ? $this->route('notification_subscription')->id : '';
        $userId = $this->route('notification_subscription') ? $this->route('notification_subscription')->user_id : Auth::id();

        return [
            'name' => [
                $required, 'string', 'min:3',
                Rule::unique('notification_subscriptions')->where('user_id', $userId)->ignore($ignore)
            ],
        ];
    }
}
