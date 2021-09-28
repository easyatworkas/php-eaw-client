<?php

namespace Eaw\Models;

use Eaw\Traits\HasProperties;

class Customer extends Model
{
    use HasProperties;

    protected $path = '/customers';

    public function employees()
    {
        return $this->client->query($this->getFullPath() . '/employees')->setModel(Employee::class);
    }
}
