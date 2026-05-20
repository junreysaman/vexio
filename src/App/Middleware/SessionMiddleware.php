<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Config\Paths;
use App\Exceptions\SessionException;
use App\Session\PredisSessionHandler;
use App\Support\RedisBootstrap;
use Framework\Contracts\MiddlewareInterface;

class SessionMiddleware implements MiddlewareInterface
{
    public function process(callable $next): mixed
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new SessionException('Session is already active.');
        }

        if (headers_sent($filename, $line)) {
            throw new SessionException("Headers were already sent from {$filename}:{$line}.");
        }

        session_set_cookie_params([
            'secure' => ($_ENV['APP_ENV'] ?? 'production') === 'production',
            'httponly' => true,
            'samesite' => 'lax',
        ]);

        $usePredisSessions = false;
        if (RedisBootstrap::sessionRedisEnabledFromEnv()) {
            $sessionDb = (int) ($_ENV['REDIS_SESSION_DB'] ?? $_ENV['REDIS_DB'] ?? 0);
            $predis = RedisBootstrap::createPredisClient($sessionDb);
            if ($predis !== null) {
                $prefix = (string) ($_ENV['REDIS_SESSION_PREFIX'] ?? 'vexio:sess:');
                $ttl = (int) ini_get('session.gc_maxlifetime');
                if ($ttl < 60) {
                    $ttl = 1440;
                }
                session_set_save_handler(new PredisSessionHandler($predis, $prefix, $ttl), true);
                $usePredisSessions = true;
            }
        }

        if (!$usePredisSessions) {
            if (!is_dir(Paths::SESSIONS)) {
                mkdir(Paths::SESSIONS, 0775, true);
            }

            session_save_path(Paths::SESSIONS);
        }

        session_start();

        try {
            return $next();
        } finally {
            session_write_close();
        }
    }
}
