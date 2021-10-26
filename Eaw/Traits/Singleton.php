<?php

namespace Eaw\Traits;

use Exception;

trait Singleton
{
    protected static $instance;

    /**
     * @deprecated This method is provided for testing purposes only.
     */
    public static function setInstance(self $instance)
    {
        static::$instance = $instance;
    }

    public static function getInstance()
    {
        return static::$instance ?: static::$instance = new static();
    }

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    protected function __wakeup()
    {
        throw new Exception('Cannot unserialize a singleton.');
    }
}
