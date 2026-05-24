<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use App\Services\Watch\WatchService;
use App\Services\Watch\CommentService;
use App\Support\MediaImage;
use App\Support\Seo;
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
        $title = $this->movieTitle($item);
        $description = Seo::description((string) ($item['synopsis'] ?? ''), 160);
        $canonicalUrl = (string) ($item['watchUrl'] ?? ($item['watch_url'] ?? ''));
        $metaImage = MediaImage::ogImageFromRow($item) ?: null;
        
        return $response->html($this->view->render(
            'frontend/watch/watch-movie/index',
            'layouts/frontend/paper',
            [
                'title' => $title,
                'body_class' => 'paper-watch-watch-movie',
                'meta_description' => $description,
                'meta_keywords' => (string) ($item['genres'] ?? ''),
                'meta_image' => $metaImage,
                'meta_image_alt' => trim((string) ($item['title'] ?? 'Movie poster')),
                'canonical_url' => $canonicalUrl,
                'structured_data' => $this->structuredData($item, $title, $description, $canonicalUrl, $metaImage),
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
            'robots' => 'noindex, nofollow',
            'message' => 'The requested movie is not published or does not exist.',
        ]), 404);
    }

    private function movieTitle(array $item): string
    {
        $name = trim((string) ($item['title'] ?? ''));
        $year = (int) ($item['release_year'] ?? 0);

        if ($name === '') {
            return 'Watch Movie';
        }

        return 'Watch ' . $name . ($year > 0 ? ' (' . $year . ')' : '') . ' Online';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function structuredData(array $item, string $title, string $description, string $canonicalUrl, ?string $image): array
    {
        $name = trim((string) ($item['title'] ?? $title));
        $genres = $this->splitList((string) ($item['genres'] ?? ''));
        $releaseDate = trim((string) ($item['release_date'] ?? ''));
        $year = (int) ($item['release_year'] ?? 0);

        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Movie',
                'name' => $name,
                'url' => Seo::canonicalUrl($canonicalUrl),
                'description' => $description,
                'image' => $image !== null ? Seo::absoluteUrl($image) : null,
                'datePublished' => $releaseDate !== '' ? $releaseDate : ($year > 0 ? (string) $year : null),
                'genre' => $genres,
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
                ['name' => 'Movies', 'url' => '/archive/browse?type=movie'],
                ['name' => $name !== '' ? $name : 'Movie', 'url' => $canonicalUrl],
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
