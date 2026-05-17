<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use App\Services\Watch\WatchService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class WatchController
{
    public function __construct(
        private TemplateEngine $view,
        private WatchService $watch
    ) {
    }

    public function movie(Request $request, Response $response, string $tmdbId): Response
    {
        $data = $this->watch->movie((int) $tmdbId);

        if (!$data) {
            return $response->html($this->view->render('frontend/errors/not-found', 'layouts/frontend/paper', [
                'title' => 'Movie Not Found',
                'body_class' => 'paper-not-found-page',
                'message' => 'The requested movie is not published or does not exist.',
            ]), 404);
        }

        return $response->html($this->movieTemplate($data['item'], $data['related'] ?? []));
    }

    public function tvshow(Request $request, Response $response, string $tmdbId, string $seasonNo, string $episodeNo): Response
    {
        $data = $this->watch->episode((int) $tmdbId, (int) $seasonNo, (int) $episodeNo);

        if (!$data) {
            return $response->html($this->view->render('frontend/errors/not-found', 'layouts/frontend/paper', [
                'title' => 'Episode Not Found',
                'body_class' => 'paper-not-found-page',
                'message' => 'The requested TV episode is not published or does not exist.',
            ]), 404);
        }

        return $response->html($this->tvTemplate($data));
    }

    public function tvshowRoot(Request $request, Response $response, string $tmdbId): Response
    {
        $data = $this->watch->firstEpisode((int) $tmdbId);

        if (!$data) {
            return $response->html($this->view->render('frontend/errors/not-found', 'layouts/frontend/paper', [
                'title' => 'TV Show Not Found',
                'body_class' => 'paper-not-found-page',
                'message' => 'The requested TV show is not published or does not have a published episode yet.',
            ]), 404);
        }

        return $response->html($this->tvTemplate($data));
    }

    private function movieTemplate(array $item, array $related): string
    {
        $html = $this->template('vexio-watch.html');
        $title = $this->e((string) ($item['title'] ?? 'Untitled Movie'));
        $year = $this->e((string) ($item['release_year'] ?: 'Movie'));
        $synopsis = $this->e((string) ($item['synopsis'] ?: 'No synopsis available.'));
        $rating = $this->e((string) ($item['tmdb_rating'] ?: 'N/A'));
        $poster = $this->asset((string) (($item['poster_image'] ?? '') ?: ($item['poster_url'] ?? '')));
        $backdrop = $this->asset((string) (($item['backdrop_image'] ?? '') ?: (($item['poster_image'] ?? '') ?: ($item['poster_url'] ?? ''))));
        $titleHtml = $this->splitTitleHtml((string) ($item['title'] ?? 'Untitled Movie'), 'var(--accent2)');

        $html = str_replace([
            'VEXIO — Watch: Neon Requiem (2024)',
            'NEON <span style="color:var(--accent2)">REQUIEM</span>',
            'Click to play · 2024 · 2h 18m',
            'Neon Requiem (2024) — Full Movie [4K]',
            'Neon Requiem',
            'NEON<br/>REQUIEM<br/>2024',
            'Movie · Sci-Fi · 2024',
            'Neon <span>Requiem</span>',
            '8.7',
            '2024',
            'In a rain-soaked megacity where neon bleeds into memory, a decommissioned AI awakens to discover it carries the last consciousness of a dying civilization — and must decide if humanity deserves to survive its own creation.',
            'Share your thoughts on Neon Requiem…',
            'Neon Requiem (2024)',
            '▶ Playing Neon Requiem (2024) · 4K HDR',
        ], [
            'VEXIO — Watch: ' . $title . ' (' . $year . ')',
            $titleHtml,
            'Click to play · ' . $year . ' · Full Movie',
            $title . ' (' . $year . ') — Full Movie [4K]',
            $title,
            $this->posterWords((string) ($item['title'] ?? 'Untitled Movie'), $year),
            'Movie · TMDB #' . (int) ($item['tmdb_id'] ?? 0) . ' · ' . $year,
            $titleHtml,
            $rating,
            $year,
            $synopsis,
            'Share your thoughts on ' . $title . '…',
            $title . ' (' . $year . ')',
            '▶ Playing ' . $title . ' (' . $year . ') · 4K HDR',
        ], $html);

        return $this->injectWatchAssets($html, $poster, $backdrop, '.movie-poster');
    }

    private function tvTemplate(array $data): string
    {
        $show = $data['show'];
        $episode = $data['episode'];
        $html = $this->template('vexio-tvshow-watch.html');
        $title = $this->e((string) ($show['title'] ?? 'Untitled TV Show'));
        $episodeTitle = $this->e((string) ($episode['title'] ?? 'Untitled Episode'));
        $seasonNo = (int) ($episode['season_number'] ?? 1);
        $episodeNo = (int) ($episode['episode_number'] ?? 1);
        $epCode = 'S' . $seasonNo . ' E' . $episodeNo;
        $synopsis = $this->e((string) ($episode['synopsis'] ?: ($show['synopsis'] ?? 'No synopsis available.')));
        $poster = $this->asset((string) (($show['poster_image'] ?? '') ?: ($show['poster_url'] ?? '')));
        $backdrop = $this->asset((string) (($episode['backdrop_image'] ?? '') ?: (($show['backdrop_image'] ?? '') ?: $poster)));
        $titleHtml = $this->splitTitleHtml((string) ($show['title'] ?? 'Untitled TV Show'), 'var(--cyan)');

        $html = str_replace([
            'VEXIO — Watch: Stellar Drift · S2 E7 · "The Void Between Stars"',
            'STELLAR <span style="color:var(--cyan)">DRIFT</span>',
            '"The Void Between Stars" · 47m',
            '← Back to Stellar Drift',
            'Stellar Drift — S2:E7 "The Void Between Stars" [4K HDR]',
            'Stellar Drift',
            'S2 E7',
            'STELLAR<br/>DRIFT',
            'TV Series · Sci-Fi · 2022 – Present',
            'Stellar <span>Drift</span>',
            'S2 E7 — "The Void Between Stars"',
            'Resume S2 E7',
            'When Earth\'s last generation-ship loses contact with its destination colony, Commander Yara Nyx must navigate a fractured crew, rogue AI mutinies, and the terrifying silence that suggests they are not alone in the void.',
            '"The Void Between Stars"',
            '▶ Playing S2 E7 — "The Void Between Stars" · 4K HDR',
        ], [
            'VEXIO — Watch: ' . $title . ' · ' . $epCode . ' · "' . $episodeTitle . '"',
            $titleHtml,
            '"' . $episodeTitle . '" · Episode ' . $episodeNo,
            '← Back to ' . $title,
            $title . ' — S' . $seasonNo . ':E' . $episodeNo . ' "' . $episodeTitle . '" [4K HDR]',
            $title,
            $epCode,
            $this->posterWords((string) ($show['title'] ?? 'TV Show')),
            'TV Show · TMDB #' . (int) ($show['tmdb_id'] ?? 0),
            $titleHtml,
            $epCode . ' — "' . $episodeTitle . '"',
            'Resume ' . $epCode,
            $synopsis,
            '"' . $episodeTitle . '"',
            '▶ Playing ' . $epCode . ' — "' . $episodeTitle . '" · 4K HDR',
        ], $html);

        return $this->injectWatchAssets($html, $poster, $backdrop, '.show-poster');
    }

    private function template(string $filename): string
    {
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $filename;

        return (string) file_get_contents($path);
    }

    private function injectWatchAssets(string $html, string $poster, string $backdrop, string $posterSelector): string
    {
        $css = '<style>
.player-backdrop{background-image:linear-gradient(135deg,rgba(0,0,0,.28),rgba(0,0,0,.68)),url("' . $backdrop . '")!important;background-size:cover!important;background-position:center!important;}
' . $posterSelector . '{background-image:url("' . $poster . '")!important;background-size:cover!important;background-position:center!important;color:transparent!important;text-shadow:none!important;}
' . $posterSelector . ' .movie-poster-badge,' . $posterSelector . ' .show-poster-badge{color:#fff!important;}
</style>';

        return str_replace('</head>', $css . "\n</head>", $html);
    }

    private function splitTitleHtml(string $title, string $accent): string
    {
        $words = preg_split('/\s+/', trim($title)) ?: [$title];
        $last = count($words) > 1 ? array_pop($words) : '';
        $first = $this->e(implode(' ', $words) ?: $title);

        return $last === ''
            ? $first
            : $first . ' <span style="color:' . $accent . '">' . $this->e($last) . '</span>';
    }

    private function posterWords(string $title, string $suffix = ''): string
    {
        $words = array_slice(preg_split('/\s+/', trim($title)) ?: [$title], 0, 3);
        $parts = array_map(fn(string $word): string => $this->e($word), $words);

        if ($suffix !== '') {
            $parts[] = $this->e($suffix);
        }

        return implode('<br/>', $parts);
    }

    private function asset(string $path): string
    {
        return $this->e($path);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
