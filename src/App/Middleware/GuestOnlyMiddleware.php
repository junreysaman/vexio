<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;

class GuestOnlyMiddleware implements MiddlewareInterface
{
    public function process(callable $next): mixed
    {
        $user = $_SESSION['user'] ?? null;

        if ($user) {
            redirectTo(($user['role'] ?? null) === 'superuser' ? '/admin/dashboard' : '/');
        }

        return $next();
    }
}
