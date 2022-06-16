<?php

require(EAW_DIR . '/vendor/autoload.php');

spl_autoload_register(function ($class) {
    if (file_exists($file = EAW_DIR . '/' . str_replace('\\', '/', $class) . '.php')) {
        require($file);
    }
});
