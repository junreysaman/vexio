<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use App\Services\Watch\WatchService;
use App\Services\Watch\CommentService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class WatchTvController
{
    public function __construct(
        private TemplateEngine $view,
        private WatchService $watch,
        private CommentService $comments
    ) {
    }

    public function root(Request $request, Response $response, string $tmdbId, ?string $slug = null): Response
    {
        $data = $this->watch->firstEpisode((int) $tmdbId);

        if (!$data) {
            return $this->notFound(
                $response,
                'TV Show Not Found',
                'The requested TV show is not published or does not have a published episode yet.'
            );
        }

        return $this->renderWatchPage($response, $data);
    }

    public function episode(Request $request, Response $response, string $tmdbId, string $slug, string $seasonNo, string $episodeNo): Response
    {
        $data = $this->watch->episode((int) $tmdbId, (int) $seasonNo, (int) $episodeNo);

        if (!$data) {
            return $this->notFound($response, 'Episode Not Found', 'The requested TV episode is not published or does not exist.');
        }

        return $this->renderWatchPage($response, $data);
    }

    public function legacyEpisode(Request $request, Response $response, string $tmdbId, string $seasonNo, string $episodeNo): Response
    {
        return $this->episode($request, $response, $tmdbId, '', $seasonNo, $episodeNo);
    }

    public function episodeById(Request $request, Response $response, string $tmdbId, string $episodeId): Response
    {
        $data = $this->watch->episodeById((int) $tmdbId, (int) $episodeId);

        if (!$data) {
            return $this->notFound($response, 'Episode Not Found', 'The requested TV episode is not published or does not exist.');
        }

        return $this->renderWatchPage($response, $data);
    }

    private function renderWatchPage(Response $response, array $data): Response
    {
        $show = $data['show'] ?? [];
        $episode = $data['episode'] ?? [];

        return $response->html($this->view->render(
            'frontend/watch/watch-tv/index',
            'layouts/frontend/paper',
            [
                'title' => (string) ($show['title'] ?? 'Watch TV Show'),
                'body_class' => 'paper-watch-watch-tv',
                'show' => $show,
                'episode' => $episode,
                'seasons' => $data['seasons'] ?? [],
                'episodes' => $data['episodes'] ?? [],
                'related' => $data['related'] ?? [],
                'comments' => $this->comments->forEpisode((int) ($episode['id'] ?? 0)),
                'commentCount' => $this->comments->count('episode', (int) ($episode['id'] ?? 0)),
                'watchUrl' => $episode['watchUrl'] ?? ($episode['watch_url'] ?? null),
            ]
        ));
    }

    private function notFound(Response $response, string $title, string $message): Response
    {
        return $response->html($this->view->render('frontend/errors/not-found', 'layouts/frontend/paper', [
            'title' => $title,
            'body_class' => 'paper-not-found-page',
            'message' => $message,
        ]), 404);
    }
}
