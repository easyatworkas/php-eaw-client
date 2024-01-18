<?php

define('EAW_DIR', realpath(__DIR__ . '/..'));

require(__DIR__ . '/autoload.php');

require(EAW_DIR . '/readline.php');

require(EAW_DIR . '/functions.php');

Eaw\Init::init();
