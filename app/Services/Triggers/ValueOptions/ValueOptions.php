<?php namespace App\Services\Triggers\ValueOptions;

use App;

class ValueOptions {

    /**
     * Get select options for trigger action or condition value.
     *
     * @param string $name
     * @return mixed
     */
    public function getByName($name)
    {
        return $this->getValueOptions($name)->getOptions();
    }


    /**
     * Get instance of specified ValueOptions service class.
     *
     * @param string $name
     * @return ValueOptionsInterface
     */
    private function getValueOptions($name)
    {
        $className = $this->getClassName($name);

        return App::make('App\Services\Triggers\ValueOptions\\'.$className);
    }

    /**
     * Get class name of specified ValueOptions service.
     *
     * @param string $name
     * @return string
     */
    private function getClassName($name)
    {
        $parts = explode(':', $name);
        $parts[1] = ucfirst($parts[1]);

        return ucfirst(implode('', $parts)).'ValueOptions';
    }
}