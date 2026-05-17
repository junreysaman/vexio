<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Home\HomeController;
use App\Controllers\Archive\BrowseController;
use App\Controllers\Archive\GenrePageController;
use App\Controllers\Archive\TrendingPageController;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class AppController
{
    public function __construct(
        private TemplateEngine $view,
        private HomeController $home,
        private BrowseController $archiveBrowse,
        private GenrePageController $archiveGenrePage,
        private TrendingPageController $archiveTrendingPage
    ) {
    }


    public function notFound(Request $request, Response $response, ?string $message = null): Response
    {
        return $response->html($this->view->render('frontend/errors/not-found', 'layouts/frontend/paper', [
            'title' => 'Not Found',
            'body_class' => 'paper-not-found-page',
            'message' => $message ?? 'The page you requested could not be found.',
        ]), 404);
    }

    public function home(Request $request, Response $response): Response
    {
        return $this->home->index($request, $response);
    }

    public function archiveBrowse(Request $request, Response $response): Response
    {
        return $this->archiveBrowse->index($request, $response);
    }

    public function archiveGenrePage(Request $request, Response $response, ?string $slug = null): Response
    {
        return $this->archiveGenrePage->index($request, $response, $slug);
    }

    public function genreGenrePage(Request $request, Response $response): Response
    {
        return $this->archiveGenrePage($request, $response);
    }

    public function archiveTrendingPage(Request $request, Response $response): Response
    {
        return $this->archiveTrendingPage->index($request, $response);
    }
}
