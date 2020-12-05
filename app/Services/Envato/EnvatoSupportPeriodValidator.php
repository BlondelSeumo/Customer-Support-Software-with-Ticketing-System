<?php

namespace App\Services\Envato;

use App;
use App\Category;
use App\PurchaseCode;
use App\Tag;
use Auth;
use Carbon\Carbon;
use Common\Settings\Settings;
use Request;

class EnvatoSupportPeriodValidator
{
    /**
     * @param string $attribute
     * @param string $value
     * @param array $parameters
     * @return bool
     */
    public function validate($attribute, $value, $parameters) {
        if (app(Settings::class)->get('envato.active_support')) {
            $category =  app(Tag::class)->where('type', 'category')->find($value);
            $matchedCode = app(PurchaseCode::class)
                ->where('user_id', Request::get('user_id', Auth::id()))
                ->get()
                ->first(function(PurchaseCode $purchaseCode) use($category) {
                    return !$category || str_contains(strtolower($purchaseCode), strtolower($category->name));
                });
            return !$matchedCode || !$this->supportExpired($matchedCode);
        } else {
            return true;
        }
    }

    protected function supportExpired(PurchaseCode $code)
    {
        if ( ! $code->supported_until) {
            return false;
        }
        $supportedUntil = is_string($code->supported_until) ? Carbon::parse($code->supported_until) : $code->supported_until;
        return $supportedUntil->lessThan(Carbon::now());
    }
}
