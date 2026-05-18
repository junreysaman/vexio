<?php

declare(strict_types=1);

namespace App\Controllers\Watch;

use App\Services\Watch\CommentService;
use Framework\Http\Request;
use Framework\Http\Response;

class CommentController
{
    public function __construct(private CommentService $comments)
    {
    }

    public function store(Request $request, Response $response): Response
    {
        if (!$request->user()) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
            return $response->error('Please sign in to comment.', 401, ['csrf_token' => $_SESSION['token']]);
        }

        try {
            $comment = $this->comments->create(
                (string) $request->data('owner_type', 'item'),
                (int) $request->data('owner_id', 0),
                (string) $request->data('body', ''),
                $request->user(),
                (int) $request->data('parent_id', 0)
            );
        } catch (\InvalidArgumentException $e) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
            return $response->error($e->getMessage(), 422, ['csrf_token' => $_SESSION['token']]);
        }

        $_SESSION['token'] = bin2hex(random_bytes(32));

        return $response->created([
            'csrf_token' => $_SESSION['token'],
            'comment' => [
                'id' => (int) ($comment['id'] ?? 0),
                'parent_id' => (int) ($comment['parent_id'] ?? 0),
                'display_name' => (string) ($comment['display_name'] ?? 'Guest Viewer'),
                'body' => (string) ($comment['body'] ?? ''),
                'likes' => (int) ($comment['likes'] ?? 0),
                'created_at' => (string) ($comment['created_at'] ?? date('Y-m-d H:i:s')),
            ],
        ]);
    }
}
