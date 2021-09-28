<?php

namespace Eaw\Models;

use Eaw\Client;

abstract class Model
{
    protected $path;

    protected $keyName = 'id';

    protected $client;

    protected $attributes;

    protected $original;

    public static function newQuery(string $path = null)
    {
        return eaw()->query($path ?? static::newInstance()->getPath())->setModel(static::class);
    }

    public static function newInstance(array $attributes = [])
    {
        return new static(eaw(), $attributes);
    }

    public static function __callStatic(string $method, array $arguments)
    {
        return call_user_func_array([ static::newQuery(), $method ], $arguments);
    }

    public function __construct(Client $client, array $attributes)
    {
        $this->client = $client;
        $this->attributes = $attributes;

        if ($this->exists()) {
            $this->syncOriginal();
        }
    }

    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getFullPath()
    {
        return $this->getPath() . '/' . $this->getKey();
    }

    public function syncOriginal()
    {
        $this->original = $this->attributes;
    }

    public function getAttribute($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function getOriginal($name)
    {
        return $this->original[$name] ?? null;
    }

    public function getDirty()
    {
        $dirty = [];

        $keys = array_unique(array_merge(array_keys($this->original), array_keys($this->attributes)));

        foreach ($keys as $key) {
            if ($this->getOriginal($key) != $this->getAttribute($key)) {
                $dirty[$key] = $this->getAttribute($key);
            }
        }

        return $dirty;
    }

    public function isDirty()
    {
        return (bool) $this->getDirty();
    }

    public function getKey()
    {
        return $this->attributes[$this->keyName] ?? null;
    }

    public function exists()
    {
        return $this->getKey() !== null;
    }

    public function save()
    {
        // TODO: Replace these with QueryBuilder?
        if ($this->exists()) {
            $this->attributes = $this->client->update($this->getFullPath(), [], $this->getDirty());
        } else {
            $this->attributes = $this->client->create($this->getPath(), [], $this->attributes);
        }

        $this->syncOriginal();

        return true;
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
}
