<?php

declare(strict_types=1);

namespace App\Support;

use App\Cache\CacheInterface;
use App\Cache\NullCache;
use App\Cache\RedisCache;
use Predis\Client;
use Throwable;

/**
 * Redis via Predis (pure PHP). No phpredis extension required.
 */
final class RedisBootstrap
{
    public static function predisAvailable(): bool
    {
        return class_exists(Client::class);
    }

    public static function redisEnabledFromEnv(): bool
    {
        return filter_var($_ENV['REDIS_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public static function sessionRedisEnabledFromEnv(): bool
    {
        if (!self::redisEnabledFromEnv()) {
            return false;
        }

        return filter_var($_ENV['REDIS_SESSION_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array{host: string, port: int, password: string, timeout: float}
     */
    public static function connectionParams(): array
    {
        return [
            'host' => (string) ($_ENV['REDIS_HOST'] ?? '127.0.0.1'),
            'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
            'password' => (string) ($_ENV['REDIS_PASSWORD'] ?? ''),
            'timeout' => (float) ($_ENV['REDIS_TIMEOUT'] ?? 2.0),
        ];
    }

    public static function createPredisClient(int $database): ?Client
    {
        if (!self::predisAvailable()) {
            return null;
        }

        $p = self::connectionParams();
        $parameters = [
            'scheme' => 'tcp',
            'host' => $p['host'],
            'port' => $p['port'],
            'database' => max(0, $database),
            'timeout' => $p['timeout'],
        ];

        if ($p['password'] !== '') {
            $parameters['password'] = $p['password'];
        }

        try {
            $client = new Client($parameters);
            $client->ping();

            return $client;
        } catch (Throwable) {
            return null;
        }
    }

    public static function makeCache(): CacheInterface
    {
        if (!self::redisEnabledFromEnv() || !self::predisAvailable()) {
            return new NullCache();
        }

        $database = (int) ($_ENV['REDIS_DB'] ?? 0);
        $client = self::createPredisClient($database);
        if ($client === null) {
            return new NullCache();
        }

        $prefix = (string) ($_ENV['REDIS_PREFIX'] ?? 'vexio:');
        if ($prefix !== '' && !str_ends_with($prefix, ':')) {
            $prefix .= ':';
        }

        return new RedisCache($client, $prefix);
    }
}
