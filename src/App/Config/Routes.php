<?php 
declare(strict_types=1);

namespace App\Config;

use App\Controllers\Admin\Content\ContentController;
use App\Controllers\Admin\CommentController as AdminCommentController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ImporterController;
use App\Controllers\Admin\UserController;
use App\Controllers\Auth\AuthController;
use App\Controllers\Auth\AuthPageController;
use App\Controllers\Search\SearchController;
use App\Controllers\Watch\CommentController;
use App\Controllers\Watch\CoreStreamController;
use App\Controllers\Watch\WatchMovieController;
use App\Controllers\Watch\WatchTvController;
use App\Controllers\AppController;
use App\Controllers\SupportPageController;
use App\Controllers\PublicSeoController;
use App\Middleware\AdminRequiredMiddleware;
use App\Middleware\AuthRequiredMiddleware;
use Framework\App;

function registerRoutes(App $app): void
{
    $routes = [
        // Public/App Routes
        ['GET', '/', [AppController::class, 'home']],
        ['GET', '/genres', [AppController::class, 'genreGenrePage']],
        ['GET', '/genre/{slug}', [AppController::class, 'archiveGenrePage']],
        ['GET', '/networks', [AppController::class, 'networkNetworkPage']],
        ['GET', '/network/{slug}', [AppController::class, 'archiveNetworkPage']],
        // Archive Routes
        ['GET', '/archive/browse', [AppController::class, 'archiveBrowse']],
        ['GET', '/archive/genres', [AppController::class, 'archiveGenrePage']],
        ['GET', '/archive/networks', [AppController::class, 'archiveNetworkPage']],
        ['GET', '/archive/trending', [AppController::class, 'archiveTrendingPage']],
        // Auth Routes
        ['GET', '/login', [AuthPageController::class, 'login']],
        ['GET', '/register', [AuthPageController::class, 'register']],
        ['POST', '/login', [AuthController::class, 'authenticate']],
        ['POST', '/register', [AuthController::class, 'store']],
        ['POST', '/logout', [AuthController::class, 'logout'], [AuthRequiredMiddleware::class]],
        // Admin Dashboard
        ['GET', '/admin/dashboard', [DashboardController::class, 'index'], [AdminRequiredMiddleware::class]],
        // Admin Users
        ['GET', '/admin/users', [UserController::class, 'index'], [AdminRequiredMiddleware::class]],
        ['GET', '/admin/users/create', [UserController::class, 'create'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/users', [UserController::class, 'store'], [AdminRequiredMiddleware::class]],
        ['GET', '/admin/users/{id}/edit', [UserController::class, 'edit'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/users/{id}/edit', [UserController::class, 'update'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/users/{id}/delete', [UserController::class, 'destroy'], [AdminRequiredMiddleware::class]],
        // Admin Comments
        ['GET', '/admin/comments', [AdminCommentController::class, 'index'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/comments/{id}/publish', [AdminCommentController::class, 'publish'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/comments/{id}/hide', [AdminCommentController::class, 'hide'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/comments/{id}/delete', [AdminCommentController::class, 'destroy'], [AdminRequiredMiddleware::class]],
        // Admin Content
        ['GET', '/admin/content', [ContentController::class, 'index'], [AdminRequiredMiddleware::class]],
        ['GET', '/admin/content/{id}/edit', [ContentController::class, 'edit'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/edit', [ContentController::class, 'update'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/delete', [ContentController::class, 'destroy'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/bulk-delete', [ContentController::class, 'bulkDestroy'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/generate-seasons', [ContentController::class, 'generateSeasons'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/seasons/{seasonId}/generate-episodes', [ContentController::class, 'generateEpisodes'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/seasons/{seasonId}/edit', [ContentController::class, 'updateSeason'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/seasons/{seasonId}/delete', [ContentController::class, 'deleteSeason'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/episodes', [ContentController::class, 'storeEpisode'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/episodes/{episodeId}/edit', [ContentController::class, 'updateEpisode'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/episodes/{episodeId}/refresh', [ContentController::class, 'refreshEpisode'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/content/{id}/episodes/{episodeId}/delete', [ContentController::class, 'deleteEpisode'], [AdminRequiredMiddleware::class]],
        // Admin Importer
        ['GET', '/admin/importer', [ImporterController::class, 'index'], [AdminRequiredMiddleware::class]],
        ['GET', '/admin/importer/results', [ImporterController::class, 'results'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/importer/import', [ImporterController::class, 'import'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/importer/hydrate-images', [ImporterController::class, 'hydrateImages'], [AdminRequiredMiddleware::class]],
        ['POST', '/admin/importer/generate-missing-tv', [ImporterController::class, 'generateMissingTvContent'], [AdminRequiredMiddleware::class]],
        // Crawlers / monetization (Google Search Console, ads.txt validators)
        ['GET', '/robots.txt', [PublicSeoController::class, 'robots']],
        ['GET', '/sitemap.xml', [PublicSeoController::class, 'sitemap']],
        ['GET', '/ads.txt', [PublicSeoController::class, 'adsTxt']],
        // Support/Info Pages
        ['GET', '/faq', [SupportPageController::class, 'faq']],
        ['GET', '/contact', [SupportPageController::class, 'contact']],
        ['GET', '/report-issue', [SupportPageController::class, 'reportIssue']],
        ['GET', '/request-title', [SupportPageController::class, 'requestTitle']],
        ['GET', '/privacy-policy', [SupportPageController::class, 'privacyPolicy']],
        ['GET', '/terms-of-use', [SupportPageController::class, 'termsOfUse']],
        ['GET', '/dmca', [SupportPageController::class, 'dmca']],
        ['GET', '/advertise', [SupportPageController::class, 'advertise']],
        // API Routes
        ['GET', '/api/search', [SearchController::class, 'index']],
        ['GET', '/api/core/sources', [CoreStreamController::class, 'sources']],
        ['GET', '/api/core/proxy', [CoreStreamController::class, 'proxy']],
        ['POST', '/api/comments', [CommentController::class, 'store']],
        // Vexio stream player
        ['GET', '/core-player/{type}/{tmdbId}', [CoreStreamController::class, 'player']],
        // Watch/Movie Routes
        ['GET', '/movie/{tmdbId}', [WatchMovieController::class, 'index']],
        ['GET', '/watch/movie/{tmdbId}', [WatchMovieController::class, 'index']],
        ['GET', '/watch/movie/{tmdbId}/{slug}', [WatchMovieController::class, 'index']],
        // Watch/TV Routes
        ['GET', '/tvshow/{tmdbId}', [WatchTvController::class, 'root']],
        ['GET', '/tvshow/{tmdbId}/{seasonNo}/{episodeNo}', [WatchTvController::class, 'legacyEpisode']],
        ['GET', '/watch/tvshow/{tmdbId}', [WatchTvController::class, 'root']],
        ['GET', '/watch/tvshow/{tmdbId}/{slug}', [WatchTvController::class, 'root']],
        ['GET', '/watch/tvshow/{tmdbId}/episode/{episodeId}', [WatchTvController::class, 'episodeById']],
        ['GET', '/watch/tvshow/{tmdbId}/{seasonNo}/{episodeNo}', [WatchTvController::class, 'legacyEpisode']],
        ['GET', '/watch/tvshow/{tmdbId}/{slug}/season/{seasonNo}/episode/{episodeNo}', [WatchTvController::class, 'episode']],
        // Error Pages
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
