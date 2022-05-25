<?php

require(__DIR__ . '/../vendor/autoload.php');

spl_autoload_register(function ($class) {
    if (file_exists($file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php')) {
        require($file);
    }
});
