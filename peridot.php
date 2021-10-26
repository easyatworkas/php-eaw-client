<?php

use Evenement\EventEmitterInterface;
use Peridot\Plugin\Prophecy\ProphecyPlugin;

// Bootstrapping without triggering automatic authentication.
require('bootstrap/autoload.php');
require('functions.php');

return function (EventEmitterInterface $emitter) {
    // Register mocker.
    new ProphecyPlugin($emitter);
};
