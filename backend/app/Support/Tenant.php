<?php

namespace App\Support;

use RuntimeException;

class Tenant
{
    protected static ?int $id = null;

    public static function set(int $id): void
    {
        static::$id = $id;
    }

    public static function clear(): void
    {
        static::$id = null;
    }

    public static function id(): int
    {
        if (static::$id === null) {
            throw new RuntimeException('Tenant context is not set');
        }

        return static::$id;
    }

    public static function get(): ?int
    {
        return static::$id;
    }

    public static function has(): bool
    {
        return static::$id !== null;
    }
}
