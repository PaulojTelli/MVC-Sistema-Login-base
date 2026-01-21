<?php

function env(string $key, $default = null)
{
    if (isset($_ENV[$key])) {
        $value = $_ENV[$key];
    } elseif (isset($_SERVER[$key])) {
        $value = $_SERVER[$key];
    } else {
        return $default;
    }

    $lower = strtolower($value);

    return match ($lower) {
        'true'  => true,
        'false' => false,
        'null'  => null,
        default => is_numeric($value) ? $value + 0 : $value,
    };
}
