<?php

namespace Eaw\Models;

use Eaw\Traits\HasProperties;

class Employee extends Model
{
    use HasProperties;

    protected $path = '/customers/{customer}/employees';
}
