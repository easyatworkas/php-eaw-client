<?php

/**
 * @return mixed
 */
function env(string $variable, string $default = null)
{
    static $dotenv;

    if (!$dotenv) {
        $dotenv = \Dotenv\Dotenv::createMutable(__DIR__);

        $dotenv->safeLoad();
    }

    return $_ENV[strtoupper($variable)] ?? $default;
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
