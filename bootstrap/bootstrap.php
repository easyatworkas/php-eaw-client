<?php

define('EAW_DIR', realpath(__DIR__ . '/..'));

require(__DIR__ . '/autoload.php');

require(EAW_DIR . '/readline.php');

require(EAW_DIR . '/functions.php');

(function () {
    if (eaw()->isAuthenticated()) {
        return true;
    }

    if ((null !== $username = env('eaw_username')) && (null !== $password = env('eaw_password'))) {
        return eaw()->userAuth($username, $password);
    }

    if ((null !== $clientId = env('eaw_client_id')) && (null !== $clientSecret = env('eaw_client_secret'))) {
        return eaw()->clientAuth($clientId, $clientSecret);
    }

    return false;
})();
