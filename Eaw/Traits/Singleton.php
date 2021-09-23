<?php

namespace Eaw\Traits;

use Exception;

trait Singleton
{
    protected static $instance;

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
