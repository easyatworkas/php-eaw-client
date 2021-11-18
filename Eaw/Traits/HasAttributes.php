<?php

namespace Eaw\Traits;

trait HasAttributes
{
    protected $attributes;

    protected $original;

    public function setAttributes(array $attributes)
    {
        return $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    public function setAttribute($name, $value)
    {
        return $this->attributes[$name] = $value;
    }

    public function getAttribute($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function unsetAttribute($name)
    {
        unset($this->attributes[$name]);
    }

    public function syncOriginal()
    {
        $this->original = $this->attributes;
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

    // Laravel-like

    public function toArray()
    {
        return $this->getAttributes();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    // Magic

    public function __isset($name)
    {
        return $this->hasAttribute($name);
    }

    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    public function __unset($name) {
        $this->unsetAttribute($name);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    // ArrayAccess

    public function offsetExists($offset)
    {
        return $this->hasAttribute($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->unsetAttribute($offset);
    }

    // JsonSerializable

    public function jsonSerialize()
    {
        return $this->getAttributes();
    }
}
