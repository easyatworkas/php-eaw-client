<?php

use Eaw\Logger as L;

$customers = eaw()->readPaginated('/customers');

$allEmployees = [];

foreach ($customers as $customer) {
    logger()->info($customer['name'] . ' ...');

    eaw()->readAsync("/customers/{$customer['id']}/employees", [ 'per_page' => 9999 ])
        ->then(function (array $response) use (&$allEmployees, $customer) {
            foreach ($response['data'] as $employee) {
                $allEmployees[] = [
                    'depno' => $customer['number'],
                    'depname' => $customer['name'],
                    'number' => $employee['number'],
                    'name' => $employee['name'],
                    'email' => $employee['email'],
                ];
            }

            logger()->info($customer['name'] . ' ' . logger()->color('OK', L::LIGHT + L::BLUE));
        });

    eaw()->tick();
}

eaw()->execute();

tabelize($allEmployees);

exit;
