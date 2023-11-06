<?php

// Example of copying data between two API instances.
// Disclaimer: This exact method of creating shifts is probably not going to work, but that's not the focus of this example.

// Switch to some other API instance.
eaw()->setBaseUrl('https://demo.api.easyatwork.com');

$shifts = [];

// Temporarily switch to a different set of credentials for this API.
eaw()->withCredentials('.auth.demo.json', function () use (&$shifts) {
    // Authenticate with the demo API. Ideally you'd prepare this file beforehand to avoid storing credentials in your code.
    eaw()->userAuth('bill@example.com', 'password123!');

    $shifts = eaw()->readPaginated('/customers/1/schedules/1/shifts')->all();
});

// Switch back to the production API.
eaw()->setBaseUrl('https://api.easyatwork.com');

// Write the read data to the production API.
foreach ($shifts as $shift) {
    eaw()->createAsync('/customers/1/schedules/1/shifts', null, $shift);

    eaw()->tick();
}

eaw()->execute();

exit;
