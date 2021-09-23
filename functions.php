<?php

/**
 * @return mixed
 */
function env(string $variable)
{
    static $dotenv;

    if (!$dotenv) {
        $dotenv = \Dotenv\Dotenv::createMutable(__DIR__);

        $dotenv->safeLoad();
    }

    return $_ENV[strtoupper($variable)] ?? null;
}

/**
 * @return \Eaw\Client
 */
function eaw()
{
    return \Eaw\Client::getInstance();
}
