<?php

declare(strict_types=1);

namespace Switch\Config;

use Dotenv\Dotenv;

class Env
{
    /**
     * @var array<string, mixed>
     */
    private static array $variables = [];

    /**
     * Load environment variables from a .env file or directory.
     */
    public static function load(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        // 1. Use vlucas/phpdotenv if available (Standard Framework Env Loader)
        if (class_exists(Dotenv::class)) {
            try {
                $dir = dirname($filePath);
                $file = basename($filePath);
                $dotenv = Dotenv::createMutable($dir, $file);
                $loaded = $dotenv->safeLoad();

                foreach ($loaded as $key => $val) {
                    $parsed = self::parseValue($val);
                    self::$variables[$key] = $parsed;
                    $_ENV[$key] = $parsed;
                    $_SERVER[$key] = $parsed;
                    putenv("{$key}={$val}");
                }

                return;
            } catch (\Throwable) {
                // Fallback to native parser
            }
        }

        // 2. High-performance native fallback parser
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
     * Load environment variables from a base directory.
     */
    public static function loadFromDirectory(string $directory, string $file = '.env'): void
    {
        self::load(rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $file);
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
     * Set an environment variable in runtime memory.
     */
    public static function set(string $key, mixed $value): void
    {
        self::$variables[$key] = $value;
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        if (is_scalar($value)) {
            putenv("{$key}={$value}");
        }
    }

    /**
     * Clear loaded environment variables (for testing isolation).
     */
    public static function clear(): void
    {
        self::$variables = [];
    }

    /**
     * Parse raw string value into appropriate PHP data type.
     */
    public static function parseValue(mixed $value): mixed
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
