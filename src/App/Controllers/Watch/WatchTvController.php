<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use App\Services\Watch\WatchService;
use App\Support\MediaImage;
use App\Services\Watch\CommentService;
use App\Support\Seo;
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
        $title = $this->episodeTitle($show, $episode);
        $description = Seo::description((string) ($episode['synopsis'] ?? $show['synopsis'] ?? ''), 160);
        $canonicalUrl = (string) ($episode['watchUrl'] ?? ($episode['watch_url'] ?? ''));
        $metaImage = MediaImage::ogImageFromRow($episode) ?: MediaImage::ogImageFromRow($show) ?: null;

        return $response->html($this->view->render(
            'frontend/watch/watch-tv/index',
            'layouts/frontend/paper',
            [
                'title' => $title,
                'body_class' => 'paper-watch-watch-tv',
                'meta_description' => $description,
                'meta_keywords' => (string) ($show['genres'] ?? ''),
                'meta_image' => $metaImage,
                'meta_image_alt' => trim((string) ($show['title'] ?? 'TV show artwork')),
                'canonical_url' => $canonicalUrl,
                'structured_data' => $this->structuredData($show, $episode, $title, $description, $canonicalUrl, $metaImage),
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
            'robots' => 'noindex, nofollow',
            'message' => $message,
        ]), 404);
    }

    private function episodeTitle(array $show, array $episode): string
    {
        $showTitle = trim((string) ($show['title'] ?? 'TV Show'));
        $season = max(1, (int) ($episode['season_number'] ?? 1));
        $episodeNumber = max(1, (int) ($episode['episode_number'] ?? 1));
        $episodeTitle = trim((string) ($episode['title'] ?? ''));

        if ($episodeTitle !== '' && strcasecmp($episodeTitle, 'Series Overview') !== 0) {
            return 'Watch ' . $showTitle . ' S' . $season . ' E' . $episodeNumber . ' - ' . $episodeTitle;
        }

        return 'Watch ' . $showTitle . ' Season ' . $season . ' Episode ' . $episodeNumber;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function structuredData(array $show, array $episode, string $title, string $description, string $canonicalUrl, ?string $image): array
    {
        $showTitle = trim((string) ($show['title'] ?? 'TV Show'));
        $episodeName = trim((string) ($episode['title'] ?? $title));
        $season = max(1, (int) ($episode['season_number'] ?? 1));
        $episodeNumber = max(1, (int) ($episode['episode_number'] ?? 1));
        $genres = $this->splitList((string) ($show['genres'] ?? ''));
        $releaseDate = trim((string) ($episode['air_date'] ?? $episode['release_date'] ?? $show['release_date'] ?? ''));

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'TVEpisode',
                'name' => $episodeName !== '' ? $episodeName : $title,
                'url' => Seo::canonicalUrl($canonicalUrl),
                'description' => $description,
                'image' => $image !== null ? Seo::absoluteUrl($image) : null,
                'datePublished' => $releaseDate !== '' ? $releaseDate : null,
                'episodeNumber' => $episodeNumber,
                'partOfSeason' => [
                    '@type' => 'TVSeason',
                    'seasonNumber' => $season,
                ],
                'partOfSeries' => [
                    '@type' => 'TVSeries',
                    'name' => $showTitle,
                    'url' => Seo::canonicalUrl((string) ($show['watchUrl'] ?? ($show['watch_url'] ?? ''))),
                    'genre' => $genres,
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $title,
                'url' => Seo::canonicalUrl($canonicalUrl),
                'description' => $description,
                'primaryImageOfPage' => $image !== null ? Seo::absoluteUrl($image) : null,
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => (string) ($_ENV['APP_NAME'] ?? 'Vexio HD'),
                    'url' => Seo::origin() . '/',
                ],
            ],
            Seo::breadcrumb([
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'TV Shows', 'url' => '/archive/browse?type=tv_show'],
                ['name' => $showTitle, 'url' => (string) ($show['watchUrl'] ?? ($show['watch_url'] ?? $canonicalUrl))],
                ['name' => 'S' . $season . ' E' . $episodeNumber, 'url' => $canonicalUrl],
            ]),
        ];
    }

    /**
     * @return list<string>
     */
    private function splitList(string $value): array
    {
        return array_values(array_filter(array_map(
            static fn(string $part): string => trim($part),
            explode(',', $value)
        ), static fn(string $part): bool => $part !== ''));
    }
}
