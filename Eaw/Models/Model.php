<?php

namespace Eaw\Models;

use ArrayAccess;
use Eaw\Client;
use Eaw\QueryBuilder;
use Eaw\Traits\HasAttributes;
use Eaw\Traits\HasTimestamps;
use JsonSerializable;

abstract class Model implements ArrayAccess, JsonSerializable
{
    use HasAttributes;
    use HasTimestamps;

    protected $path;

    protected $keyName = 'id';

    protected $client;

    /**
     * @param string|null $path
     * @return QueryBuilder<static>
     */
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

        $this->setAttributes($attributes);

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

    public function setKey($value)
    {
        return $this->setAttribute($this->keyName, $value);
    }

    public function getKey()
    {
        return $this->getAttribute($this->keyName);
    }

    public function exists()
    {
        return $this->hasAttribute($this->keyName);
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }

        return $this;
    }

    public function replicate(array $filter = [], bool $whitelist = false)
    {
        if (!$whitelist) {
            $filter = array_merge($filter, [
                $this->keyName,
                $this->createdAtColumn,
                $this->updatedAtColumn,
                '_dates',
                '_business_dates',
            ]);
        }

        return new static($this->client, call_user_func($whitelist ? 'array_intersect_key' : 'array_diff_key', $this->attributes, array_flip($filter)));
    }

    public function save()
    {
        // TODO: Replace these with QueryBuilder?
        if ($this->exists()) {
            $this->attributes = $this->client->update($this->getFullPath(), [], $this->getDirty());
        } else {
            $this->attributes = $this->client->create($this->getPath(), [], $this->getAttributes());
        }

        $this->syncOriginal();

        return true;
    }

    public function update(array $attributes)
    {
        return $this->fill($attributes)->save();
    }

    public function delete()
    {
        if (!$this->exists()) {
            return false;
        }

        $this->client->delete($this->getFullPath());

        return true;
    }
}
