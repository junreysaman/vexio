<?php

declare(strict_types=1);

namespace App\Config;

use App\Middleware\CsrfGuardMiddleware;
use App\Middleware\CsrfTokenMiddleware;
use App\Middleware\FlashMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use App\Middleware\SessionMiddleware;
use App\Middleware\TemplateDataMiddleware;
use App\Middleware\ValidationExceptionMiddleware;
use Framework\App;

function registerMiddleware(App $app): void
{
    $app->addMiddleware(CsrfGuardMiddleware::class);
    $app->addMiddleware(CsrfTokenMiddleware::class);
    $app->addMiddleware(TemplateDataMiddleware::class);
    $app->addMiddleware(ValidationExceptionMiddleware::class);
    $app->addMiddleware(FlashMiddleware::class);
    $app->addMiddleware(SessionMiddleware::class);
    $app->addMiddleware(SecurityHeadersMiddleware::class);
}
