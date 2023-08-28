<?php

namespace Eaw\Traits;

use Exception;

trait IsSingleton
{
    /** @var static */
    protected static $instance;

    /**
     * @deprecated This method is provided for testing purposes only.
     */
    public static function setInstance(self $instance)
    {
        static::$instance = $instance;
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance ?: static::$instance = new static();
    }

    /**
     * Overridden to prevent instantiation.
     */
    protected function __construct()
    {

    }

    /**
     * Overridden to prevent cloning.
     */
    protected function __clone()
    {

    }

    /**
     * Overridden to prevent deserialization.
     *
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize a singleton.');
    }
}
