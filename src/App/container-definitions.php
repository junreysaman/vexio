<?php

declare(strict_types=1);

use App\Config\Paths;
use App\Controllers\Admin\Content\ContentController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ImporterController;
use App\Controllers\Admin\UserController;
use App\Controllers\AppController;
use App\Controllers\Auth\AuthController;
use App\Controllers\Auth\AuthPageController;
use App\Controllers\Watch\WatchController;
use App\Controllers\Home\HomeController;
use App\Services\Admin\DashboardService;
use App\Services\Admin\Content\ContentService;
use App\Services\Admin\UserService;
use App\Services\Auth\AuthService;
use App\Services\Home\HomeService;
use App\Services\Media\MediaCatalogService;
use App\Services\TMDB\TmdbImporterService;
use App\Controllers\Archive\BrowseController;
use App\Controllers\Archive\GenrePageController;
use App\Controllers\Archive\TrendingPageController;
use App\Services\Archive\BrowseService;
use App\Services\Archive\GenrePageService;
use App\Controllers\Watch\WatchMovieController;
use App\Controllers\Watch\WatchTvController;
use Framework\Database;
use Framework\TemplateEngine;

return [
    TemplateEngine::class => fn() => new TemplateEngine(Paths::VIEW),
    Database::class => fn() => new Database(
        (string) ($_ENV['DB_DRIVER'] ?? 'mysql'),
        [
            'host' => (string) ($_ENV['DB_HOST'] ?? 'localhost'),
            'port' => (string) ($_ENV['DB_PORT'] ?? '3306'),
            'dbname' => (string) ($_ENV['DB_NAME'] ?? 'paper_phpframework'),
        ],
        (string) ($_ENV['DB_USER'] ?? 'root'),
        (string) ($_ENV['DB_PASSWORD'] ?? '')
    ),
    AppController::class => fn($container) => $container->resolve(AppController::class),
    HomeController::class => fn($container) => $container->resolve(HomeController::class),
    AuthPageController::class => fn($container) => $container->resolve(AuthPageController::class),
    AuthController::class => fn($container) => $container->resolve(AuthController::class),
    DashboardController::class => fn($container) => $container->resolve(DashboardController::class),
    ContentController::class => fn($container) => $container->resolve(ContentController::class),
    ImporterController::class => fn($container) => $container->resolve(ImporterController::class),
    UserController::class => fn($container) => $container->resolve(UserController::class),
    WatchController::class => fn($container) => $container->resolve(WatchController::class),
    AuthService::class => fn($container) => $container->resolve(AuthService::class),
    HomeService::class => fn($container) => $container->resolve(HomeService::class),
    MediaCatalogService::class => fn($container) => $container->resolve(MediaCatalogService::class),
    TmdbImporterService::class => fn($container) => $container->resolve(TmdbImporterService::class),
    DashboardService::class => fn($container) => $container->resolve(DashboardService::class),
    ContentService::class => fn($container) => $container->resolve(ContentService::class),
    UserService::class => fn($container) => $container->resolve(UserService::class),
    BrowseController::class => fn($container) => $container->resolve(BrowseController::class),
    GenrePageController::class => fn($container) => $container->resolve(GenrePageController::class),
    TrendingPageController::class => fn($container) => $container->resolve(TrendingPageController::class),
    BrowseService::class => fn($container) => new BrowseService(fn() => $container->get(Database::class)),
    GenrePageService::class => fn($container) => new GenrePageService(fn() => $container->get(Database::class)),
    WatchMovieController::class => fn($container) => $container->resolve(WatchMovieController::class),
    WatchTvController::class => fn($container) => $container->resolve(WatchTvController::class),
];
