<?php

namespace Eaw\Models;

use Eaw\Client;

class Model
{
    protected static $basePath = '/';

    /**
     * @param string $key
     * @return static
     */
    public static function find(string $key)
    {
        // TODO: This couples the underlying storage (the HTTP API) with the model logic. Abstraction would be good.
        $response = Client::getInstance()->read(static::$basePath . '/' . $key);

        return new static($response);
    }

    protected $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}
