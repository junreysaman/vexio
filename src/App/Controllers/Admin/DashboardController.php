<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Admin\DashboardService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class DashboardController
{
    public function __construct(
        private TemplateEngine $view,
        private DashboardService $dashboard
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->html($this->view->render('admin/dashboard/index', 'layouts/backend/paper', [
            'title' => 'Admin Dashboard',
            'body_class' => 'paper-backend admin-dashboard',
            ...$this->dashboard->overview(),
        ]));
    }
}
