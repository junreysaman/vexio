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
use App\Controllers\Search\SearchController;
use App\Controllers\Watch\WatchMovieController;
use App\Controllers\Watch\WatchTvController;
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
        ['POST', '/admin/content/{id}/seasons/{seasonId}/generate-episodes', [ContentController::class, 'generateEpisodes']],
        ['POST', '/admin/content/{id}/seasons/{seasonId}/edit', [ContentController::class, 'updateSeason']],
        ['POST', '/admin/content/{id}/seasons/{seasonId}/delete', [ContentController::class, 'deleteSeason']],
        ['POST', '/admin/content/{id}/episodes', [ContentController::class, 'storeEpisode']],
        ['POST', '/admin/content/{id}/episodes/{episodeId}/edit', [ContentController::class, 'updateEpisode']],
        ['POST', '/admin/content/{id}/episodes/{episodeId}/delete', [ContentController::class, 'deleteEpisode']],
        ['GET', '/admin/importer', [ImporterController::class, 'index']],
        ['GET', '/admin/importer/results', [ImporterController::class, 'results']],
        ['POST', '/admin/importer/import', [ImporterController::class, 'import']],
        ['GET', '/archive/browse', [AppController::class, 'archiveBrowse']],
        ['GET', '/archive/genres', [AppController::class, 'archiveGenrePage']],
        ['GET', '/archive/trending', [AppController::class, 'archiveTrendingPage']],
        ['GET', '/api/search', [SearchController::class, 'index']],
        ['GET', '/movie/{tmdbId}', [WatchMovieController::class, 'index']],
        ['GET', '/tvshow/{tmdbId}', [WatchTvController::class, 'root']],
        ['GET', '/tvshow/{tmdbId}/{seasonNo}/{episodeNo}', [WatchTvController::class, 'legacyEpisode']],
        ['GET', '/watch/movie/{tmdbId}', [WatchMovieController::class, 'index']],
        ['GET', '/watch/movie/{tmdbId}/{slug}', [WatchMovieController::class, 'index']],
        ['GET', '/watch/tvshow/{tmdbId}', [WatchTvController::class, 'root']],
        ['GET', '/watch/tvshow/{tmdbId}/{slug}', [WatchTvController::class, 'root']],
        ['GET', '/watch/tvshow/{tmdbId}/episode/{episodeId}', [WatchTvController::class, 'episodeById']],
        ['GET', '/watch/tvshow/{tmdbId}/{seasonNo}/{episodeNo}', [WatchTvController::class, 'legacyEpisode']],
        ['GET', '/watch/tvshow/{tmdbId}/{slug}/season/{seasonNo}/episode/{episodeNo}', [WatchTvController::class, 'episode']],
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
