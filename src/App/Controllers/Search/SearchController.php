<?php

declare(strict_types=1);

namespace App\Controllers\Search;

use App\Services\Search\SearchService;
use Framework\Http\Request;
use Framework\Http\Response;

class SearchController
{
    public function __construct(private SearchService $search)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        $query = (string) $request->query('q', '');

        return $response->json([
            'results' => $this->search->live($query),
        ]);
    }
}
