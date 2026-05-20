<?php

declare(strict_types=1);

namespace App\Cache;

final class NullCache implements CacheInterface
{
    public function get(string $key): mixed
    {
        return null;
    }

    public function set(string $key, mixed $value, int $ttlSeconds): bool
    {
        return false;
    }

    public function delete(string $key): bool
    {
        return false;
    }
}
