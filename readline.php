<?php

if (!function_exists('readline')) {
    function readline(string $prompt = null)
    {
        if (!is_null($prompt)) {
            echo $prompt;
        }

        return stream_get_line(STDIN, 1024, PHP_EOL);
    }
}

if (!function_exists('readline_info')) {
    function readline_info(string $var = null, $value = null)
    {
        if ($var !== null) {
            return null;
        }

        return [];
    }
}

if (!function_exists('readline_completion_function')) {
    function readline_completion_function(callable $function)
    {
        return true;
    }
}

if (!function_exists('readline_read_history')) {
    function readline_read_history(string $file = null)
    {
        return true;
    }
}

if (!function_exists('readline_clear_history')) {
    function readline_clear_history()
    {
        return true;
    }
}

if (!function_exists('readline_add_history')) {
    function readline_add_history(string $prompt)
    {
        return true;
    }
}

if (!function_exists('readline_write_history')) {
    function readline_write_history(string $file = null)
    {
        return true;
    }
}
