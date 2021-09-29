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

    // Find widest value for each colum.
    $widths = array_reduce(array_merge([ $header ], $data), function (array $carry, array &$row) {
        foreach ($row as $column => $value) {
            $carry[$column] = max($carry[$column] ?? 0, strlen($value));
        }

        return $carry;
    }, []);

    // Print header.
    foreach ($columns as $column) {
        echo str_pad($header[$column] ?? $column, $widths[$column] + $padding, '-');
    }

    echo PHP_EOL;

    // Print data.
    foreach ($data as $row) {
        foreach ($columns as $column) {
            echo str_pad($row[$column] ?? '', $widths[$column] + $padding, ' ');
        }

        echo PHP_EOL;
    }
}
