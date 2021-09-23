<?php

use Eaw\Client;
use Eaw\Models\Customer;

require('autoload.php');

$eaw = Client::getInstance();

//$eaw->clientAuth(5, 'abc123');
$eaw->userAuth('jim@easyatwork.com', 'abc123');

$customer = Customer::find(1);

print_r($customer->getAttributes());

exit;
