<?php

use Eaw\Models\Customer;

describe('Customer model', function () {
    $this->model = Customer::class;

    require('shared/mocks-client.php');
    require('shared/behaves-like-model.php');
});
