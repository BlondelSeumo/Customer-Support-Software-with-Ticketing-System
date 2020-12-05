<?php namespace App\Services\Triggers\ValueOptions;

interface ValueOptionsInterface {

    /**
     * Return select options for trigger action value.
     *
     * @return mixed
     */
    public function getOptions();
}