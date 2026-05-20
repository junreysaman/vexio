<?php

declare(strict_types=1);

namespace App\Session;

use Predis\Client;
use SessionHandlerInterface;
use Throwable;

/**
 * PHP sessions stored in Redis via Predis (no phpredis extension required).
 */
final class PredisSessionHandler implements SessionHandlerInterface
{
    public function __construct(
        private Client $client,
        private string $keyPrefix,
        private int $ttlSeconds
    ) {
        if ($this->keyPrefix !== '' && !str_ends_with($this->keyPrefix, ':')) {
            $this->keyPrefix .= ':';
        }
    }

    private function key(string $sessionId): string
    {
        return $this->keyPrefix . $sessionId;
    }

    public function open(string $path, string $name): bool
    {
        try {
            $this->client->ping();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        try {
            $value = $this->client->get($this->key($id));

            return $value === null ? '' : (string) $value;
        } catch (Throwable) {
            return false;
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $this->client->setex($this->key($id), max(60, $this->ttlSeconds), $data);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $this->client->del([$this->key($id)]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Keys use TTL; nothing to scan.
     *
     * @return int|false
     */
    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }
}
