<?php

namespace Eaw\Models;

use Eaw\Traits\HasProperties;

class PayTypeLink extends Model
{
    use HasProperties;

    protected $path = '/customers/{customer}/pay_type_links';

    protected $hasPropertyController = false;
}
