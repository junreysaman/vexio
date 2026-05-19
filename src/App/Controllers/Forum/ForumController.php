<?php

declare(strict_types=1);

namespace App\Controllers\Forum;

use App\Services\Forum\ForumService;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class ForumController
{
    public function __construct(
        private TemplateEngine $view,
        private ForumService $forum
    ) {
    }

    // GET /forum
    public function index(Request $request, Response $response): Response
    {
        $category = trim((string) $request->query('category', ''));
        $sort     = trim((string) $request->query('sort', 'latest'));
        $page     = max(1, (int) $request->query('page', 1));

        $paginated = $this->forum->paginate($category, $sort, $page);
        $stats     = $this->forum->stats();

        return $response->html($this->view->render('frontend/forum/index', 'layouts/frontend/paper', [
            'title'      => 'Community Forum',
            'body_class' => 'paper-forum',
            'threads'    => $paginated['data'],
            'meta'       => $paginated['meta'],
            'stats'      => $stats,
            'categories' => ForumService::CATEGORIES,
            'sortOptions'=> ForumService::SORT_OPTIONS,
            'activeCategory' => $category,
            'activeSort'     => $sort,
        ]));
    }

    // GET /forum/thread/{id}
    public function show(Request $request, Response $response, string $id): Response
    {
        $threadId = (int) $id;
        $thread   = $this->forum->findThread($threadId);

        if (!$thread) {
            setFlash('forum', 'Thread not found.', 'danger');
            redirectTo('/forum');
        }

        $this->forum->incrementViews($threadId);

        $page    = max(1, (int) $request->query('page', 1));
        $replies = $this->forum->replies($threadId, $page);

        return $response->html($this->view->render('frontend/forum/thread', 'layouts/frontend/paper', [
            'title'      => $thread['title'] . ' — Forum',
            'body_class' => 'paper-forum',
            'thread'     => $thread,
            'replies'    => $replies['data'],
            'replyMeta'  => $replies['meta'],
            'categories' => ForumService::CATEGORIES,
        ]));
    }

    // POST /forum/threads
    public function store(Request $request, Response $response): Response
    {
        $user = $request->user();

        if (!$user) {
            return $response->error('Please sign in to post a thread.', 401, [
                'csrf_token' => $this->refreshCsrf(),
            ]);
        }

        try {
            $thread = $this->forum->createThread($request->post(), $user);
        } catch (\InvalidArgumentException $e) {
            return $response->error($e->getMessage(), 422, [
                'csrf_token' => $this->refreshCsrf(),
            ]);
        }

        return $response->json([
            'ok'         => true,
            'thread'     => $thread,
            'csrf_token' => $this->refreshCsrf(),
        ], 201);
    }

    // POST /forum/thread/{id}/replies
    public function storeReply(Request $request, Response $response, string $id): Response
    {
        $user = $request->user();

        if (!$user) {
            return $response->error('Please sign in to reply.', 401, [
                'csrf_token' => $this->refreshCsrf(),
            ]);
        }

        $body = trim((string) $request->post('body', ''));

        try {
            $reply = $this->forum->createReply((int) $id, $body, $user);
        } catch (\InvalidArgumentException $e) {
            return $response->error($e->getMessage(), 422, [
                'csrf_token' => $this->refreshCsrf(),
            ]);
        }

        return $response->json([
            'ok'         => true,
            'reply'      => $reply,
            'csrf_token' => $this->refreshCsrf(),
        ], 201);
    }

    // POST /forum/thread/{id}/vote
    public function vote(Request $request, Response $response, string $id): Response
    {
        $user = $request->user();

        if (!$user) {
            return $response->error('Please sign in to vote.', 401, [
                'csrf_token' => $this->refreshCsrf(),
            ]);
        }

        $value = (int) $request->post('value', 1);
        $votes = $this->forum->voteThread((int) $id, (int) $user['id'], $value);

        return $response->json([
            'ok'         => true,
            'votes'      => $votes,
            'csrf_token' => $this->refreshCsrf(),
        ]);
    }

    private function refreshCsrf(): string
    {
        $_SESSION['token'] = bin2hex(random_bytes(32));
        return $_SESSION['token'];
    }
}
