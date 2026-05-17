<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Contracts\MiddlewareInterface;
use Framework\TemplateEngine;

class TemplateDataMiddleware implements MiddlewareInterface
{
    public function __construct(private TemplateEngine $view)
    {
    }

    public function process(callable $next): mixed
    {
        $this->view->addGlobal('project', $_ENV['APP_NAME'] ?? 'Paper-PHPFramework');
        $this->view->addGlobal('appUrl', $_ENV['APP_URL'] ?? '/');
        $this->view->addGlobal('year', date('Y'));
        $this->view->addGlobal('currentUser', $_SESSION['user'] ?? null);

        return $next();
    }
}
