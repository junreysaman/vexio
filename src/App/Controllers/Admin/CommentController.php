<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Admin\CommentService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class CommentController
{
    public function __construct(
        private TemplateEngine $view,
        private CommentService $comments
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $status = (string) $request->query('status', 'all');
        $search = (string) $request->query('q', '');
        $page = (int) $request->query('page', 1);
        $pagination = $this->comments->paginate($status, $search, $page);

        return $response->html($this->view->render('admin/comments/index', 'layouts/backend/paper', [
            'title' => 'Comments',
            'body_class' => 'paper-backend comments-page',
            'comments' => $pagination['data'],
            'meta' => $pagination['meta'],
            'stats' => $this->comments->stats(),
            'activeStatus' => $status,
            'query' => $search,
        ]));
    }

    public function publish(Request $request, Response $response, string $id): void
    {
        $this->comments->setStatus((int) $id, 'published');
        setFlash('comments', 'Comment published.', 'success');
        redirectTo($_SERVER['HTTP_REFERER'] ?? '/admin/comments');
    }

    public function hide(Request $request, Response $response, string $id): void
    {
        $this->comments->setStatus((int) $id, 'hidden');
        setFlash('comments', 'Comment hidden.', 'success');
        redirectTo($_SERVER['HTTP_REFERER'] ?? '/admin/comments');
    }

    public function destroy(Request $request, Response $response, string $id): void
    {
        $this->comments->delete((int) $id);
        setFlash('comments', 'Comment deleted.', 'success');
        redirectTo($_SERVER['HTTP_REFERER'] ?? '/admin/comments');
    }
}
