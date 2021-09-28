<?php

namespace Eaw\Models;

use Eaw\Traits\HasProperties;

class User extends Model
{
    use HasProperties;

    protected $path = '/users';
}
