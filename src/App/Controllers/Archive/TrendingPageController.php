<?php

declare(strict_types=1);

namespace App\Controllers\Archive;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class TrendingPageController
{
    public function __construct(private TemplateEngine $view)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->html($this->view->render('frontend/archive/trending-page/index', 'layouts/frontend/paper', [
            'title' => 'Trending    ',
            'body_class' => 'paper-archive-trending-page',
        ]));
    }
}
