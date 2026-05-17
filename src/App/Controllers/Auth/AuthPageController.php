<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class AuthPageController
{
    public function __construct(private TemplateEngine $view)
    {
    }

    public function login(Request $request, Response $response): Response
    {
        return $response->html($this->view->render('auth/login', 'layouts/frontend/paper', [
            'title' => 'Admin Login',
            'body_class' => 'auth-page',
        ]));
    }

    public function register(Request $request, Response $response): Response
    {
        return $response->html($this->view->render('auth/register', 'layouts/frontend/paper', [
            'title' => 'Create Account',
            'body_class' => 'auth-page',
        ]));
    }
}
