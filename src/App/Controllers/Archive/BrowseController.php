<?php

declare(strict_types=1);

namespace App\Controllers\Archive;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;
use App\Services\Archive\BrowseService;

class BrowseController
{
    public function __construct(
        private TemplateEngine $view,
        private BrowseService $browse,
        
        )
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->html($this->view->render(
            'frontend/archive/browse/index',
            'layouts/frontend/paper',
            $this->browse->pageData()
        ));
    }

    public function paginate(Request $request, Response $response): Response
    {
        $page = (int) ($request->query('page', 1));
        $limit = (int) ($request->query('limit', 24));

        $filters = [
            'type' => $request->query('type', 'all'),
            'genres' => $request->query('genres', ''),
            'countries' => $request->query('countries', ''),
            'rating' => (float) ($request->query('rating', 0)),
            'year_from' => (int) ($request->query('year_from', 0)),
            'year_to' => (int) ($request->query('year_to', 0)),
        ];

        // Parse comma-separated values for genres and countries
        if (is_string($filters['genres']) && $filters['genres'] !== '') {
            $filters['genres'] = array_filter(array_map('trim', explode(',', $filters['genres'])));
        } else {
            $filters['genres'] = [];
        }

        if (is_string($filters['countries']) && $filters['countries'] !== '') {
            $filters['countries'] = array_filter(array_map('trim', explode(',', $filters['countries'])));
        } else {
            $filters['countries'] = [];
        }

        $data = $this->browse->getPaginatedItems($page, $limit, $filters);

        return $response->json($data);
    }
}
