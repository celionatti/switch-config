<?php

declare(strict_types=1);

namespace Switch\Config;

use ArrayAccess;
use InvalidArgumentException;

class Config implements ArrayAccess
{
    /**
     * @var array<string, mixed>
     */
    private array $items = [];

    /**
     * @param array<string, mixed> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function loadFromDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            throw new InvalidArgumentException("Directory '{$directory}' does not exist.");
        }

        $files = glob(rtrim($directory, '/\\') . '/*.php');
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $values = require $file;
            if (is_array($values)) {
                $this->set($key, $values);
            }
        }
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        if (!str_contains($key, '.')) {
            return $this->items[$key] ?? $default;
        }

        $array = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $array = &$this->items;

        while (count($keys) > 1) {
            $k = array_shift($keys);
            if (!isset($array[$k]) || !is_array($array[$k])) {
                $array[$k] = [];
            }
            $array = &$array[$k];
        }

        $array[array_shift($keys)] = $value;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set((string) $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $key = (string) $offset;
        $keys = explode('.', $key);
        $array = &$this->items;

        while (count($keys) > 1) {
            $k = array_shift($keys);
            if (!isset($array[$k]) || !is_array($array[$k])) {
                return;
            }
            $array = &$array[$k];
        }

        unset($array[array_shift($keys)]);
    }
}
