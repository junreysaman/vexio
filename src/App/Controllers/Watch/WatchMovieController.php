<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use App\Services\Watch\WatchService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class WatchMovieController
{
    public function __construct(
        private TemplateEngine $view,
        private WatchService $watch
    ) {
    }

    public function index(Request $request, Response $response, string $tmdbId, ?string $slug = null): Response
    {
        $data = $this->watch->movie((int) $tmdbId);

        if (!$data) {
            return $this->notFound($response);
        }

        return $response->html($this->view->render(
            'frontend/watch/watch-movie/index',
            'layouts/frontend/paper',
            [
                'title' => (string) ($data['item']['title'] ?? 'Watch Movie'),
                'body_class' => 'paper-watch-watch-movie',
                'item' => $data['item'],
                'related' => $data['related'] ?? [],
                'watchUrl' => $data['item']['watchUrl'] ?? ($data['item']['watch_url'] ?? null),
            ]
        ));
    }

    private function notFound(Response $response): Response
    {
        return $response->html($this->view->render('frontend/errors/not-found', 'layouts/frontend/paper', [
            'title' => 'Movie Not Found',
            'body_class' => 'paper-not-found-page',
            'message' => 'The requested movie is not published or does not exist.',
        ]), 404);
    }

}
