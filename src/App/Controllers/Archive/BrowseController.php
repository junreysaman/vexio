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
}
