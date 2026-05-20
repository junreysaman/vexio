<?php

declare(strict_types=1);

namespace App\Cache;

use Predis\Client;
use Throwable;

final class RedisCache implements CacheInterface
{
    public function __construct(
        private Client $redis,
        private string $keyPrefix
    ) {
    }

    private function namespaced(string $key): string
    {
        return $this->keyPrefix . $key;
    }

    public function get(string $key): mixed
    {
        try {
            $raw = $this->redis->get($this->namespaced($key));
        } catch (Throwable) {
            return null;
        }

        if ($raw === null) {
            return null;
        }

        if (!is_string($raw)) {
            return null;
        }

        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }
    }

    public function set(string $key, mixed $value, int $ttlSeconds): bool
    {
        $ttlSeconds = max(1, $ttlSeconds);

        try {
            $payload = json_encode($value, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return false;
        }

        try {
            $this->redis->setex($this->namespaced($key), $ttlSeconds, $payload);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function delete(string $key): bool
    {
        try {
            return (int) $this->redis->del([$this->namespaced($key)]) > 0;
        } catch (Throwable) {
            return false;
        }
    }
}
