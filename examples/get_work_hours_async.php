<?php

use Eaw\Logger as L;

$from = readline('From date [yyyy-mm-dd]: ');
$to = readline('To date [yyyy-mm-dd]: ');

$customers = eaw()->readPaginated('/customers');

$data = [];

foreach ($customers as $customer) {
    logger()->info($customer['name'] . ' ...');

    $cb = function (string $type) use (&$data, $customer) {
        return function (array $response) use ($type, &$data, $customer) {
            foreach ($response['data'] as $entity) {
                $date = explode(' ', $entity['business_date'])[0];
                $key = $customer['id'] . '-' . $date;

                if (!array_key_exists($key, $data)) {
                    $data[$key] = [
                        'depno' => $customer['number'],
                        'depname' => $customer['name'],
                        'date' => $date,
                        'scheduled' => 0,
                        'punched' => 0,
                    ];
                }

                $data[$key][$type] += $entity['length'];
            }

            logger()->info($customer['name'] . ' ' . logger()->color($type, L::LIGHT + L::BLUE));
        };
    };

    eaw()->readAsync("/customers/{$customer['id']}/shifts", [ 'per_page' => 9999, 'from_business_date' => $from, 'to_business_date' => $to ])->then($cb('scheduled'));
    eaw()->readAsync("/customers/{$customer['id']}/timepunches", [ 'per_page' => 9999, 'from' => $from, 'to' => $to ])->then($cb('punched'));

    eaw()->tick();
}

eaw()->execute();

usort($data, function ($a, $b) {
    if ($a['depno'] < $b['depno']) {
        return -1;
    }

    if ($a['depno'] > $b['depno']) {
        return 1;
    }

    if ($a['date'] < $b['date']) {
        return -1;
    }

    if ($a['date'] > $b['date']) {
        return 1;
    }

    return 0;
});

tabelize(array_map(function (array $row) {
    $row['scheduled'] = round($row['scheduled'] / 3600, 2);
    $row['punched'] = round($row['punched'] / 3600, 2);

    return $row;
}, $data));

exit;
