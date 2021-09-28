<?php

namespace Eaw\Traits;

use Eaw\Models\Property;
use Eaw\QueryBuilder;

trait HasProperties
{
    /**
     * Get this model's properties.
     *
     * Returns a Paginator for models with their own property controller. Returns an array of Properties otherwise.
     *
     * @return QueryBuilder|Property[]
     */
    public function properties()
    {
        if ($this->hasPropertyController ?? true) {
            return $this->client->query($this->getFullPath() . '/properties')->setModel(Property::class);
        }

        if (!is_array($this->getAttribute('properties'))) {
            $withProperties = static::newQuery($this->getPath())
                ->with([ 'properties' ])
                ->get($this->getKey());

            $this->attributes['properties'] = $withProperties->getAttribute('properties') ?? [];
        }

        return array_map(function (array $attributes) {
            return Property::newInstance($attributes);
        }, $this->getAttribute('properties'));
    }
}
