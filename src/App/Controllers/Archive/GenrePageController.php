<?php

declare(strict_types=1);

namespace App\Controllers\Archive;

use App\Services\Archive\GenrePageService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class GenrePageController
{
    public function __construct(
        private TemplateEngine $view,
        private GenrePageService $genres
    )
    {
    }

    public function index(Request $request, Response $response, ?string $slug = null): Response
    {
        return $response->html($this->view->render(
            'frontend/archive/genre-page/index',
            'layouts/frontend/paper',
            $this->genres->pageData($slug)
        ));
    }
}
