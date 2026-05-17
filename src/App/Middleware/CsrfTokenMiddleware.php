<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;
use Framework\TemplateEngine;

class CsrfTokenMiddleware implements MiddlewareInterface
{
    public function __construct(private TemplateEngine $view)
    {
    }

    public function process(callable $next): mixed
    {
        $_SESSION['token'] = $_SESSION['token'] ?? bin2hex(random_bytes(32));
        $this->view->addGlobal('_csrfToken', $_SESSION['token']);

        return $next();
    }
}
