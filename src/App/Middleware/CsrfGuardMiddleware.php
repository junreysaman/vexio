<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;

class CsrfGuardMiddleware implements MiddlewareInterface
{
    public function process(callable $next): mixed
    {
        if (!in_array(strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next();
        }

        $sessionToken = $_SESSION['token'] ?? '';
        $requestToken = $_POST['token'] ?? '';

        if (!$sessionToken || !$requestToken || !hash_equals($sessionToken, $requestToken)) {
            if ($this->expectsJsonResponse()) {
                http_response_code(419);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'error' => [
                        'message' => 'CSRF token mismatch.',
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                exit;
            }

            unset($_SESSION['token']);
            $_SESSION['token'] = bin2hex(random_bytes(32));
            http_response_code(419);
            setFlash('csrf_error', 'Your session expired. Please try again.', 'danger');
            redirectTo($_SERVER['HTTP_REFERER'] ?? '/');
        }

        unset($_SESSION['token']);
        return $next();
    }

    private function expectsJsonResponse(): bool
    {
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json');
    }

}
