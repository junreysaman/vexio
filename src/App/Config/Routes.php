<?php

declare(strict_types=1);

namespace App\Config;

use App\Controllers\AppController;
use App\Controllers\Auth\AuthController;
use App\Controllers\Auth\AuthPageController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ImporterController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\Content\ContentController;
use App\Controllers\Watch\WatchController;
use Framework\App;

function registerRoutes(App $app): void
{
    $routes = [
        ['GET', '/', [AppController::class, 'home']],
        ['GET', '/genres', [AppController::class, 'genreGenrePage']],
        ['GET', '/genre/{slug}', [AppController::class, 'archiveGenrePage']],
        ['GET', '/login', [AuthPageController::class, 'login']],
        ['GET', '/register', [AuthPageController::class, 'register']],
        ['POST', '/login', [AuthController::class, 'authenticate']],
        ['POST', '/register', [AuthController::class, 'store']],
        ['POST', '/logout', [AuthController::class, 'logout']],
        ['GET', '/admin/dashboard', [DashboardController::class, 'index']],
        ['GET', '/admin/users', [UserController::class, 'index']],
        ['GET', '/admin/users/create', [UserController::class, 'create']],
        ['POST', '/admin/users', [UserController::class, 'store']],
        ['GET', '/admin/users/{id}/edit', [UserController::class, 'edit']],
        ['POST', '/admin/users/{id}/edit', [UserController::class, 'update']],
        ['POST', '/admin/users/{id}/delete', [UserController::class, 'destroy']],
        ['GET', '/admin/content', [ContentController::class, 'index']],
        ['GET', '/admin/content/{id}/edit', [ContentController::class, 'edit']],
        ['POST', '/admin/content/{id}/edit', [ContentController::class, 'update']],
        ['POST', '/admin/content/{id}/delete', [ContentController::class, 'destroy']],
        ['POST', '/admin/content/bulk-delete', [ContentController::class, 'bulkDestroy']],
        ['POST', '/admin/content/{id}/generate-seasons', [ContentController::class, 'generateSeasons']],
        ['POST', '/admin/content/{id}/generate-episodes', [ContentController::class, 'generateEpisodes']],
        ['POST', '/admin/content/{id}/seasons/{seasonId}/edit', [ContentController::class, 'updateSeason']],
        ['POST', '/admin/content/{id}/seasons/{seasonId}/delete', [ContentController::class, 'deleteSeason']],
        ['POST', '/admin/content/{id}/episodes/{episodeId}/edit', [ContentController::class, 'updateEpisode']],
        ['POST', '/admin/content/{id}/episodes/{episodeId}/delete', [ContentController::class, 'deleteEpisode']],
        ['GET', '/admin/importer', [ImporterController::class, 'index']],
        ['GET', '/admin/importer/results', [ImporterController::class, 'results']],
        ['POST', '/admin/importer/import', [ImporterController::class, 'import']],
        ['GET', '/movie/{tmdbId}', [WatchController::class, 'movie']],
        ['GET', '/tvshow/{tmdbId}', [WatchController::class, 'tvshowRoot']],
        ['GET', '/tvshow/{tmdbId}/{seasonNo}/{episodeNo}', [WatchController::class, 'tvshow']],
        ['GET', '/archive/browse', [AppController::class, 'archiveBrowse']],
        ['GET', '/archive/genres', [AppController::class, 'archiveGenrePage']],
        ['GET', '/archive/trending', [AppController::class, 'archiveTrendingPage']],
        ['GET', '/404', [AppController::class, 'notFound']],
    ];

    foreach ($routes as $route) {
        [$method, $path, $handler, $middlewares] = array_pad($route, 4, []);
        $routeRegistration = $app->{strtolower($method)}($path, $handler);

        foreach ($middlewares as $middleware) {
            $routeRegistration->add($middleware);
        }
    }

    $app->setErrorHandler([AppController::class, 'notFound']);
}
