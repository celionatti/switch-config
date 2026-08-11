<?php

declare(strict_types=1);

namespace Switch\Config;

class Env
{
    /**
     * @var array<string, mixed>
     */
    private static array $variables = [];

    /**
     * Load environment variables from a .env file.
     */
    public static function load(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and blank lines
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, '//')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip surrounding quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            // Inline comment removal if unquoted
            if (str_contains($value, ' #')) {
                $value = explode(' #', $value, 2)[0];
                $value = trim($value);
            }

            $parsedValue = self::parseValue($value);

            self::$variables[$key] = $parsedValue;
            $_ENV[$key] = $parsedValue;
            $_SERVER[$key] = $parsedValue;
            putenv("{$key}={$value}");
        }
    }

    /**
     * Get an environment variable value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$variables)) {
            return self::$variables[$key];
        }

        if (array_key_exists($key, $_ENV)) {
            return self::parseValue($_ENV[$key]);
        }

        if (array_key_exists($key, $_SERVER)) {
            return self::parseValue($_SERVER[$key]);
        }

        $envValue = getenv($key);
        if ($envValue !== false) {
            return self::parseValue($envValue);
        }

        return $default;
    }

    /**
     * Parse raw string value into appropriate PHP data type.
     */
    private static function parseValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $lower = strtolower($value);

        return match ($lower) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}
