<?php

require(__DIR__ . '/autoload.php');

require(__DIR__ . '/../functions.php');

(function () {
    if ((null !== $username = env('username')) && (null !== $password = env('password'))) {
        return eaw()->userAuth($username, $password);
    }

    if ((null !== $clientId = env('client_id')) && (null !== $clientSecret = env('client_secret'))) {
        return eaw()->clientAuth($clientId, $clientSecret);
    }
})();