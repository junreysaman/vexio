<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use App\Services\Watch\WatchService;
use App\Services\Watch\CommentService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class WatchMovieController
{
    public function __construct(
        private TemplateEngine $view,
        private WatchService $watch,
        private CommentService $comments
    ) {
    }

    public function index(Request $request, Response $response, string $tmdbId, ?string $slug = null): Response
    {
        $data = $this->watch->movie((int) $tmdbId);

        if (!$data) {
            return $this->notFound($response);
        }

        $item = $data['item'];
        
        return $response->html($this->view->render(
            'frontend/watch/watch-movie/index',
            'layouts/frontend/paper',
            [
                'title' => (string) ('Watch' . ' ' . $item['title'] . ' ' .  $item['release_year'] ?? 'Watch Movie'),
                'body_class' => 'paper-watch-watch-movie',
                'meta_description' => $this->truncate((string) ($item['synopsis'] ?? ''), 160),
                'meta_keywords' => (string) ($item['genres'] ?? ''),
                'meta_image' => $item['poster_image'] ?? $item['backdrop_image'] ?? null,
                'item' => $item,
                'related' => $data['related'] ?? [],
                'comments' => $this->comments->forItem((int) ($item['id'] ?? 0)),
                'commentCount' => $this->comments->count('item', (int) ($item['id'] ?? 0)),
                'watchUrl' => $item['watchUrl'] ?? ($item['watch_url'] ?? null),
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

    private function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length - 3) . '...';
    }

}
