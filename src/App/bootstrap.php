<?php

declare(strict_types=1);

use Framework\App;
use Dotenv\Dotenv;

$root = dirname(__DIR__, 2);

require $root . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

Dotenv::createImmutable($root)->safeLoad();

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');

require __DIR__ . '/Config/Routes.php';
require __DIR__ . '/Config/Middleware.php';

$app = new App(__DIR__ . '/container-definitions.php');

\App\Config\registerRoutes($app);
\App\Config\registerMiddleware($app);

return $app;
