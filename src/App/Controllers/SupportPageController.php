<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\TemplateEngine;

class SupportPageController
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $pages;

    public function __construct(private TemplateEngine $view)
    {
        /** @var array<string, array<string, mixed>> $loaded */
        $loaded = require __DIR__ . '/../Config/support-pages.php';
        $this->pages = $loaded;
    }

    public function faq(Request $request, Response $response): Response
    {
        return $this->show($request, $response, 'faq');
    }

    public function contact(Request $request, Response $response): Response
    {
        return $this->show($request, $response, 'contact');
    }

    public function reportIssue(Request $request, Response $response): Response
    {
        return $this->show($request, $response, 'report-issue');
    }

    public function requestTitle(Request $request, Response $response): Response
    {
        return $this->show($request, $response, 'request-title');
    }

    public function privacyPolicy(Request $request, Response $response): Response
    {
        return $this->show($request, $response, 'privacy-policy');
    }

    public function termsOfUse(Request $request, Response $response): Response
    {
        return $this->show($request, $response, 'terms-of-use');
    }

    public function dmca(Request $request, Response $response): Response
    {
        return $this->show($request, $response, 'dmca');
    }

    public function advertise(Request $request, Response $response): Response
    {
        return $this->show($request, $response, 'advertise');
    }

    private function show(Request $request, Response $response, string $slug): Response
    {
        if (!isset($this->pages[$slug])) {
            return $response->html($this->view->render('frontend/errors/not-found', 'layouts/frontend/paper', [
                'title' => 'Not Found',
                'body_class' => 'paper-not-found-page',
                'robots' => 'noindex, nofollow',
                'message' => 'The support page you requested could not be found.',
            ]), 404);
        }

        $page = $this->pages[$slug];
        $supportEmail = trim((string) ($_ENV['APP_SUPPORT_EMAIL'] ?? ''));
        $legalEmail = trim((string) ($_ENV['APP_LEGAL_EMAIL'] ?? ''));

        $defaultSidebar = [
            'heading' => 'Explore VEXIO',
            'body' => 'Browse trending titles, search the catalogue, or read the FAQ for quick answers.',
            'actions' => [
                ['label' => 'Trending', 'href' => '/archive/trending'],
                ['label' => 'Browse', 'href' => '/archive/browse'],
                ['label' => 'FAQ', 'href' => '/faq'],
            ],
        ];

        return $response->html($this->view->render('frontend/support/page', 'layouts/frontend/paper', [
            ...$page,
            'slug' => $slug,
            'body_class' => 'paper-support-page',
            'supportEmail' => $supportEmail,
            'legalEmail' => $legalEmail,
            'sidebar' => is_array($page['sidebar'] ?? null) ? $page['sidebar'] : $defaultSidebar,
        ]));
    }
}
