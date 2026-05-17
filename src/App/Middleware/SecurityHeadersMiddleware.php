<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function process(callable $next): mixed
    {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
            header('Cross-Origin-Resource-Policy: same-origin');

            if ($this->isProductionHttps()) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }

        return $next();
    }

    private function isProductionHttps(): bool
    {
        $isProduction = ($_ENV['APP_ENV'] ?? 'production') === 'production';
        $appUrl = (string) ($_ENV['APP_URL'] ?? '');
        $isHttps = (($_SERVER['HTTPS'] ?? '') !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || str_starts_with($appUrl, 'https://');

        return $isProduction && $isHttps;
    }
}
