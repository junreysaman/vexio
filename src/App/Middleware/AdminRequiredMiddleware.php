<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;

class AdminRequiredMiddleware implements MiddlewareInterface
{
    public function process(callable $next): mixed
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            redirectTo('/login');
        }

        if (($user['role'] ?? null) !== 'superuser') {
            setFlash('auth_error', 'Admin access is required for that area.', 'danger');
            redirectTo('/');
        }

        return $next();
    }
}
