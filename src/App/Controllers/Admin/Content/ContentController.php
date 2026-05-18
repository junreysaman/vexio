<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Content;

use App\Services\Admin\Content\ContentService;
use App\Services\TMDB\TmdbImporterService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;
use RuntimeException;

class ContentController
{
    public function __construct(
        private TemplateEngine $view,
        private ContentService $content,
        private TmdbImporterService $tmdb
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $type = $this->type((string) $request->query('type', 'all'), true);
        $status = $this->status((string) $request->query('status', 'all'), true);
        $search = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $paginated = $this->content->paginate($type, $search, $status, $page, 20);

        return $response->html($this->view->render('admin/content/index', 'layouts/backend/paper', [
            'title' => 'Content',
            'body_class' => 'paper-backend content-page',
            'items' => $paginated['data'],
            'meta' => $paginated['meta'],
            'types' => ContentService::TYPES,
            'statuses' => ContentService::STATUSES,
            'stats' => $this->content->stats(),
            'activeType' => $type,
            'activeStatus' => $status,
            'query' => $search,
        ]));
    }

    public function edit(Request $request, Response $response, string $id): Response
    {
        $item = $this->findOrRedirect((int) $id);

        return $response->html($this->view->render('admin/content/form', 'layouts/backend/paper', [
            'title' => 'Edit Content',
            'body_class' => 'paper-backend content-page',
            'item' => $item,
            'hierarchy' => $this->content->hierarchy((int) $item['id']),
            'types' => array_diff_key(ContentService::TYPES, ['all' => true]),
            'statuses' => ContentService::STATUSES,
        ]));
    }

    public function update(Request $request, Response $response, string $id): void
    {
        $contentId = (int) $id;
        $this->findOrRedirect($contentId);
        $data = $this->contentData($request);
        $this->validate($data, $contentId);

        $this->content->update($contentId, $data);
        setFlash('content', 'Content updated successfully.', 'success');
        redirectTo('/admin/content/' . $contentId . '/edit');
    }

    public function destroy(Request $request, Response $response, string $id): void
    {
        $contentId = (int) $id;
        $item = $this->findOrRedirect($contentId);
        $hierarchy = $this->content->hierarchy($contentId);

        $this->content->delete($contentId);
        $this->deleteContentAssets($item, $hierarchy);

        setFlash('content', 'Deleted "' . $item['title'] . '".', 'success');
        redirectTo('/admin/content?type=' . urlencode((string) $item['type']));
    }

    /**
     * Deletes selected top-level media items and lets the service remove their child rows.
     */
    public function bulkDestroy(Request $request, Response $response): void
    {
        $ids = $request->post('ids', []);
        $ids = is_array($ids) ? $ids : [];
        $items = [];

        foreach ($ids as $id) {
            $item = $this->content->find((int) $id);

            if ($item) {
                $items[] = [
                    'item' => $item,
                    'hierarchy' => $this->content->hierarchy((int) $item['id']),
                ];
            }
        }

        $deleted = $this->content->bulkDelete($ids);

        foreach ($items as $entry) {
            $this->deleteContentAssets($entry['item'], $entry['hierarchy']);
        }

        setFlash('content', 'Deleted ' . number_format(count($deleted)) . ' content item(s).', 'success');
        redirectTo('/admin/content');
    }

    public function generateSeasons(Request $request, Response $response, string $id): void
    {
        $contentId = (int) $id;
        $item = $this->findOrRedirect($contentId);
        $type = (string) ($item['type'] ?? '');

        if ($type !== 'tv_show' || empty($item['tmdb_id'])) {
            setFlash('content', 'Season import is only available for imported TV shows.', 'danger');
            redirectTo('/admin/content');
        }

        try {
            $result = $this->tmdb->generateTvSeasons(
                (int) $item['tmdb_id'],
                (string) ($request->post('status', $item['status'] ?? 'draft'))
            );

            setFlash(
                'content',
                'Imported ' . number_format((int) $result['seasons']) . ' season(s) for "' . $item['title'] . '".',
                'success'
            );
        } catch (RuntimeException $exception) {
            setFlash('content', $exception->getMessage(), 'danger');
        }

        redirectTo('/admin/content/' . $contentId . '/edit');
    }

    public function generateEpisodes(Request $request, Response $response, string $id, string $seasonId): void
    {
        $contentId = (int) $id;
        $seasonId = (int) $seasonId;
        $item = $this->findOrRedirect($contentId);
        $season = $this->content->findSeason($contentId, $seasonId);

        if (!$season || (string) ($item['type'] ?? '') !== 'tv_show' || empty($item['tmdb_id'])) {
            setFlash('content', 'Episode import is only available for imported TV show seasons.', 'danger');
            redirectTo('/admin/content/' . $contentId . '/edit#seasons');
        }

        try {
            $result = $this->tmdb->generateTvEpisodesForSeason(
                (int) $item['tmdb_id'],
                (int) $season['season_number'],
                (string) ($request->post('status', $season['status'] ?? $item['status'] ?? 'draft'))
            );

            $message = 'Imported ' . number_format((int) $result['episodes']) . ' episode(s)';
            if (!empty($result['skipped'])) {
                $message .= ', skipped ' . number_format((int) $result['skipped']);
            }
            $message .= ' for season ' . (int) $season['season_number'] . '.';
            
            if (!empty($result['errors'])) {
                $message .= ' Errors: ' . implode('; ', $result['errors']);
            }
            
            setFlash(
                'content',
                $message,
                !empty($result['errors']) ? 'warning' : 'success'
            );
        } catch (RuntimeException $exception) {
            setFlash('content', 'Episode import failed: ' . $exception->getMessage(), 'danger');
        }

        redirectTo('/admin/content/' . $contentId . '/edit#episodes');
    }

    public function updateSeason(Request $request, Response $response, string $id, string $seasonId): void
    {
        $contentId = (int) $id;
        $seasonId = (int) $seasonId;
        $this->findOrRedirect($contentId);
        $season = $this->content->findSeason($contentId, $seasonId);

        if (!$season) {
            setFlash('content', 'Season could not be found for this title.', 'danger');
            redirectTo('/admin/content/' . $contentId . '/edit');
        }

        $data = $this->seasonData($request);

        if ($data['title'] === '' || !$this->content->validStatus((string) $data['status'])) {
            setFlash('content', 'Season title and status are required.', 'danger');
            redirectTo('/admin/content/' . $contentId . '/edit');
        }

        $this->content->updateSeason($contentId, $seasonId, $data);
        setFlash('content', 'Season updated successfully.', 'success');
        redirectTo('/admin/content/' . $contentId . '/edit#seasons');
    }

    public function deleteSeason(Request $request, Response $response, string $id, string $seasonId): void
    {
        $contentId = (int) $id;
        $season = $this->content->findSeason($contentId, (int) $seasonId);

        if ($season) {
            $this->content->deleteSeason($contentId, (int) $seasonId);
            $this->deleteLocalAsset($season['poster_image'] ?? null);
            $this->deleteLocalAsset($season['backdrop_image'] ?? null);
            setFlash('content', 'Season deleted. Episodes from that season were kept for review.', 'success');
        } else {
            setFlash('content', 'Season could not be found for this title.', 'danger');
        }

        redirectTo('/admin/content/' . $contentId . '/edit#seasons');
    }

    public function updateEpisode(Request $request, Response $response, string $id, string $episodeId): void
    {
        $contentId = (int) $id;
        $episodeId = (int) $episodeId;
        $this->findOrRedirect($contentId);
        $episode = $this->content->findEpisode($contentId, $episodeId);

        if (!$episode) {
            setFlash('content', 'Episode could not be found for this title.', 'danger');
            redirectTo('/admin/content/' . $contentId . '/edit');
        }

        $data = $this->episodeData($request);

        if ($data['title'] === '' || !$this->content->validStatus((string) $data['status'])) {
            setFlash('content', 'Episode title and status are required.', 'danger');
            redirectTo('/admin/content/' . $contentId . '/edit');
        }

        if ($this->content->episodeNumberExists($contentId, (int) $data['season_number'], (int) $data['episode_number'], $episodeId)) {
            setFlash('content', 'An episode already exists for that season and episode number.', 'danger');
            redirectTo('/admin/content/' . $contentId . '/edit#episodes');
        }

        $this->content->updateEpisode($contentId, $episodeId, $data);
        setFlash('content', 'Episode updated successfully.', 'success');
        redirectTo('/admin/content/' . $contentId . '/edit#episodes');
    }

    public function storeEpisode(Request $request, Response $response, string $id): void
    {
        $contentId = (int) $id;
        $item = $this->findOrRedirect($contentId);

        if ((string) ($item['type'] ?? '') !== 'tv_show') {
            setFlash('content', 'Manual episodes can only be added to TV shows.', 'danger');
            redirectTo('/admin/content/' . $contentId . '/edit');
        }

        $data = $this->episodeData($request);

        if ($data['title'] === '' || !$this->content->validStatus((string) $data['status'])) {
            setFlash('content', 'Episode title and status are required.', 'danger');
            redirectTo('/admin/content/' . $contentId . '/edit#episodes');
        }

        if ($this->content->episodeNumberExists($contentId, (int) $data['season_number'], (int) $data['episode_number'])) {
            setFlash('content', 'An episode already exists for that season and episode number.', 'danger');
            redirectTo('/admin/content/' . $contentId . '/edit#episodes');
        }

        $this->content->createEpisode($contentId, $data);
        setFlash('content', 'Episode added successfully.', 'success');
        redirectTo('/admin/content/' . $contentId . '/edit#episodes');
    }

    public function deleteEpisode(Request $request, Response $response, string $id, string $episodeId): void
    {
        $contentId = (int) $id;
        $episode = $this->content->findEpisode($contentId, (int) $episodeId);

        if ($episode) {
            $this->content->deleteEpisode($contentId, (int) $episodeId);
            $this->deleteLocalAsset($episode['poster_image'] ?? null);
            $this->deleteLocalAsset($episode['backdrop_image'] ?? null);
            setFlash('content', 'Episode deleted.', 'success');
        } else {
            setFlash('content', 'Episode could not be found for this title.', 'danger');
        }

        redirectTo('/admin/content/' . $contentId . '/edit#episodes');
    }

    private function contentData(Request $request): array
    {
        return [
            'title' => trim((string) $request->post('title', '')),
            'slug' => trim((string) $request->post('slug', '')),
            'type' => (string) $request->post('type', ''),
            'synopsis' => trim((string) $request->post('synopsis', '')),
            'poster_url' => trim((string) $request->post('poster_url', '')),
            'poster_image' => trim((string) $request->post('poster_image', '')),
            'backdrop_image' => trim((string) $request->post('backdrop_image', '')),
            'stream_link' => trim((string) $request->post('stream_link', '')),
            'release_year' => trim((string) $request->post('release_year', '')),
            'is_featured' => (int) $request->post('is_featured', 0),
            'tmdb_rating' => trim((string) $request->post('tmdb_rating', '')),
            'tmdb_popularity' => trim((string) $request->post('tmdb_popularity', '')),
            'tmdb_vote_count' => (int) $request->post('tmdb_vote_count', 0),
            'views' => (int) $request->post('views', 0),
            'status' => (string) $request->post('status', 'draft'),
        ];
    }

    private function seasonData(Request $request): array
    {
        return [
            'title' => trim((string) $request->post('title', '')),
            'synopsis' => trim((string) $request->post('synopsis', '')),
            'poster_url' => trim((string) $request->post('poster_url', '')),
            'poster_image' => trim((string) $request->post('poster_image', '')),
            'backdrop_image' => trim((string) $request->post('backdrop_image', '')),
            'season_number' => (int) $request->post('season_number', 1),
            'release_year' => trim((string) $request->post('release_year', '')),
            'status' => (string) $request->post('status', 'draft'),
        ];
    }

    private function episodeData(Request $request): array
    {
        return [
            'title' => trim((string) $request->post('title', '')),
            'synopsis' => trim((string) $request->post('synopsis', '')),
            'poster_url' => trim((string) $request->post('poster_url', '')),
            'poster_image' => trim((string) $request->post('poster_image', '')),
            'backdrop_image' => trim((string) $request->post('backdrop_image', '')),
            'stream_link' => trim((string) $request->post('stream_link', '')),
            'episode_name' => trim((string) $request->post('episode_name', '')),
            'season_number' => (int) $request->post('season_number', 1),
            'episode_number' => (int) $request->post('episode_number', 1),
            'release_year' => trim((string) $request->post('release_year', '')),
            'views' => (int) $request->post('views', 0),
            'status' => (string) $request->post('status', 'draft'),
        ];
    }

    private function validate(array $data, int $contentId): void
    {
        if ($data['title'] === '') {
            $this->backWithError('Title is required.', $data, $contentId);
        }

        if (!$this->content->validType((string) $data['type'])) {
            $this->backWithError('Choose a valid content type.', $data, $contentId);
        }

        if (!$this->content->validStatus((string) $data['status'])) {
            $this->backWithError('Choose a valid publishing status.', $data, $contentId);
        }

        foreach (['release_year'] as $field) {
            if ($data[$field] !== '' && !ctype_digit((string) $data[$field])) {
                $this->backWithError('Year must be numeric.', $data, $contentId);
            }
        }
    }

    private function findOrRedirect(int $id): array
    {
        $item = $id > 0 ? $this->content->find($id) : null;

        if (!$item) {
            setFlash('content', 'The requested content could not be found.', 'danger');
            redirectTo('/admin/content');
        }

        return $item;
    }

    private function backWithError(string $message, array $data, int $contentId): void
    {
        setFlash('content', $message, 'danger');
        $_SESSION['oldFormData'] = array_diff_key($data, ['token' => true]);
        redirectTo('/admin/content/' . $contentId . '/edit');
    }

    private function type(string $type, bool $allowAll = false): string
    {
        if ($allowAll && $type === 'all') {
            return 'all';
        }

        return $this->content->validType($type) ? $type : 'all';
    }

    private function status(string $status, bool $allowAll = false): string
    {
        if ($allowAll && $status === 'all') {
            return 'all';
        }

        return $this->content->validStatus($status) ? $status : 'all';
    }

    /**
     * Removes local poster and backdrop files for a deleted title, its seasons, and its episodes.
     */
    private function deleteContentAssets(array $item, array $hierarchy): void
    {
        $this->deleteLocalAsset($item['poster_image'] ?? null);
        $this->deleteLocalAsset($item['backdrop_image'] ?? null);

        foreach ([...($hierarchy['seasons'] ?? []), ...($hierarchy['episodes'] ?? [])] as $row) {
            $this->deleteLocalAsset($row['poster_image'] ?? null);
            $this->deleteLocalAsset($row['backdrop_image'] ?? null);
        }
    }

    private function deleteLocalAsset(?string $path): void
    {
        if (!$path || !str_starts_with($path, '/uploads/')) {
            return;
        }

        $publicPath = realpath(dirname(__DIR__, 5) . '/public');
        $absolutePath = realpath(($publicPath ?: '') . DIRECTORY_SEPARATOR . ltrim($path, '/'));

        if (!$publicPath || !$absolutePath || !str_starts_with($absolutePath, $publicPath)) {
            return;
        }

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}
