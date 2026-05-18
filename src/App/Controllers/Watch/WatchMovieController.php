<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class WatchMovieController
{
    public function __construct(private TemplateEngine $view)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->html($this->view->render('frontend/watch/watch-movie/index', 'layouts/frontend/paper', [
            'title' => 'Watch Watch Movie',
            'body_class' => 'paper-watch-watch-movie',
        ]));
    }
}
