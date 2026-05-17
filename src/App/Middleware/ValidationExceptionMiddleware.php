<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;
use Framework\Exceptions\ValidationException;

class ValidationExceptionMiddleware implements MiddlewareInterface
{
    public function process(callable $next): mixed
    {
        try {
            return $next();
        } catch (ValidationException $e) {
            $_SESSION['errors'] = $e->errors;
            $_SESSION['oldFormData'] = array_diff_key($_POST, array_flip(['password', 'token']));

            redirectTo($_SERVER['HTTP_REFERER'] ?? '/authentication/v3/login');
        }
    }
}
