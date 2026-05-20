<?php

declare(strict_types=1);

namespace App\Cache;

interface CacheInterface
{
    /**
     * @return mixed|null Decoded value or null on miss / disabled cache.
     */
    public function get(string $key): mixed;

    /**
     * @param mixed $value Must be JSON-serializable.
     */
    public function set(string $key, mixed $value, int $ttlSeconds): bool;

    public function delete(string $key): bool;
}
