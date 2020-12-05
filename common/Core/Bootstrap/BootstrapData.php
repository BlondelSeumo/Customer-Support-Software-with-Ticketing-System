<?php


namespace Common\Core\Bootstrap;


interface BootstrapData
{
    /**
     * Get data needed to bootstrap the application.
     *
     * @return string
     */
    public function getEncoded();

    /**
     * @return self
     */
    public function init();
}