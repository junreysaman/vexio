<?php

declare(strict_types=1);

namespace App\Controllers\Home;

use App\Services\Home\HomeService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class HomeController
{
    public function __construct(
        private TemplateEngine $view,
        private HomeService $home
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->html($this->view->render(
            'frontend/home/index',
            'layouts/frontend/paper',
            $this->home->pageData()
        ));
    }
}
