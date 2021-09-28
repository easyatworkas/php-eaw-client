<?php

namespace Eaw\Traits;

use Eaw\Models\Property;

trait HasProperties
{
    public function properties()
    {
        return $this->client->query($this->getFullPath() . '/properties')->setModel(Property::class);
    }
}
