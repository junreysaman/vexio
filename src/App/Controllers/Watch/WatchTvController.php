<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class WatchTvController
{
    public function __construct(private TemplateEngine $view)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->html($this->view->render('frontend/watch/watch-tv/index', 'layouts/frontend/paper', [
            'title' => 'Watch Watch Tv',
            'body_class' => 'paper-watch-watch-tv',
        ]));
    }
}
