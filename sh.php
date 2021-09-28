<?php

require(__DIR__ . '/bootstrap/bootstrap.php');

(function () {
    if (method_exists($eaw = eaw(), $command = $_SERVER['argv'][1] ?? null)) {
        echo json_encode(call_user_func_array([ $eaw, $command ], array_slice($_SERVER['argv'], 2)), JSON_PRETTY_PRINT);
        exit;
    }
})();

call_user_func(Psy\bin());
