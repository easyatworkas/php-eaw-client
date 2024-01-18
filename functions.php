<?php

/**
 * @return mixed
 */
function env(string $variable, string $default = null)
{
    static $dotenv;

    if (!$dotenv) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);

        $dotenv->safeLoad();
    }

    $value = getenv(strtoupper($variable));

    return $value === false ? $default : $value;
}

/**
 * @return \Eaw\Client
 */
function eaw()
{
    return \Eaw\Client::getInstance();
}

/**
 * @return \Monolog\Logger
 */
function logger(string $name = null)
{
    return \Eaw\Logger::getInstance()->getLogger($name);
}

/**
 * An attempt at secret inputs using ANSI hackery.
 *
 * Needs improvement.
 *
 * @param string|null $prompt
 * @return false|string
 */
function readline_secret(string $prompt = null)
{
    $oldFormat = \Eaw\Logger::getInstance()->getDefaultFormat();
    \Eaw\Logger::getInstance()->setDefaultFormat('{message}');

    logger()->info($prompt . '{dblack}{bg-dblack}{save}', [ 'save' => sprintf(\Eaw\Logger::ESCAPE, 's') ]);

    $input = readline();

    logger()->info('{up}{reset}{back}', [ 'up' => sprintf(\Eaw\Logger::ESCAPE, 'u'), 'back' => sprintf(\Eaw\Logger::ESCAPE, 'G') ]);

    \Eaw\Logger::getInstance()->setDefaultFormat($oldFormat);

    return $input;
}

function multiple_choice(string $message, array $options, string $default = null)
{
    $keys = array_keys($options);

    logger()->info($message);

    foreach ($options as $key => $option) {
        logger()->info("  ({$key}) {$option}");
    }

    if ($default === null) {
        $prompt = '[' . implode('/', $keys) . ']: ';
    } else {
        $prompt = '[' . implode('/', array_diff($keys, [ $default ])) . '/' . strtoupper($default) . ']: ';
    }

    do {
        $input = strtolower(readline($prompt));
    } while (!in_array($input, $keys));

    return $input;
}

function tabelize(array $data, array $header = [], int $padding = 2)
{
    // Find all column names.
    $columns = array_unique(array_reduce($data, function (array $carry, array $row) {
        return array_merge($carry, array_keys($row));
    }, []));

    // Use column name for header unless anything else is provided.
    foreach ($columns as $column) {
        $header[$column] = $header[$column] ?? $column;
    }

    // Find the widest value for each column.
    $widths = array_reduce(array_merge([ $header ], $data), function (array $carry, array &$row) {
        foreach ($row as $column => $value) {
            $carry[$column] = max($carry[$column] ?? 0, mb_strlen($value));
        }

        return $carry;
    }, []);

    // Print header.
    foreach ($columns as $column) {
        echo mb_str_pad($header[$column] ?? $column, $widths[$column] + $padding, '-');
    }

    echo PHP_EOL;

    // Print data.
    foreach ($data as $row) {
        foreach ($columns as $column) {
            echo mb_str_pad($row[$column] ?? '', $widths[$column] + $padding, ' ');
        }

        echo PHP_EOL;
    }
}

// https://stackoverflow.com/a/14773638
// Also currently provided via Peridot -> Symfony/Polyfill-Mbstring
if (!function_exists('mb_str_pad')) {
    function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = 'UTF-8')
    {
        $input_length = mb_strlen($input, $encoding);
        $pad_string_length = mb_strlen($pad_string, $encoding);

        if ($pad_length <= 0 || ($pad_length - $input_length) <= 0) {
            return $input;
        }

        $num_pad_chars = $pad_length - $input_length;

        switch ($pad_type) {
            case STR_PAD_RIGHT:
                $left_pad = 0;
                $right_pad = $num_pad_chars;
                break;

            case STR_PAD_LEFT:
                $left_pad = $num_pad_chars;
                $right_pad = 0;
                break;

            case STR_PAD_BOTH:
                $left_pad = floor($num_pad_chars / 2);
                $right_pad = $num_pad_chars - $left_pad;
                break;
        }

        $result = '';
        for ($i = 0; $i < $left_pad; ++$i) {
            $result .= mb_substr($pad_string, $i % $pad_string_length, 1, $encoding);
        }
        $result .= $input;
        for ($i = 0; $i < $right_pad; ++$i) {
            $result .= mb_substr($pad_string, $i % $pad_string_length, 1, $encoding);
        }

        return $result;
    }
}
