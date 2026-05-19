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
    private array $pages = [
        'faq' => [
            'title' => 'FAQ',
            'eyebrow' => 'Support',
            'summary' => 'Quick answers about watching, browsing, requests, ads, and account-free streaming on Vexio.',
            'badge' => 'Help Center',
            'sections' => [
                [
                    'heading' => 'Watching on Vexio',
                    'items' => [
                        ['title' => 'Do I need an account?', 'body' => 'No. Vexio is designed for fast browsing and playback without registration.'],
                        ['title' => 'Why are some titles missing?', 'body' => 'The catalogue changes as sources are refreshed. Use Request Title when you want us to prioritize a missing movie or show.'],
                        ['title' => 'How are trending titles ranked?', 'body' => 'Trending lists prioritize view activity, with freshness and catalogue signals used to keep results useful.'],
                    ],
                ],
                [
                    'heading' => 'Playback and devices',
                    'items' => [
                        ['title' => 'What should I do if a stream fails?', 'body' => 'Try another server first. If the issue continues, use Report Issue and include the title, episode, and device.'],
                        ['title' => 'Does Vexio work on mobile?', 'body' => 'Yes. Core browse, watch, slider, and support views are responsive for phone, tablet, laptop, and desktop sizes.'],
                    ],
                ],
            ],
        ],
        'contact' => [
            'title' => 'Contact',
            'eyebrow' => 'Support',
            'summary' => 'Reach the Vexio team for catalogue questions, partnership notes, moderation issues, or general feedback.',
            'badge' => 'Response Desk',
            'sections' => [
                [
                    'heading' => 'How to reach us',
                    'items' => [
                        ['title' => 'General support', 'body' => 'Send a clear note with the page URL, device, browser, and what you expected to happen.'],
                        ['title' => 'Catalogue feedback', 'body' => 'For missing metadata, broken posters, or title corrections, include the title name and release year.'],
                        ['title' => 'Business inquiries', 'body' => 'For ads or partnerships, use the Advertise page so the request reaches the right queue.'],
                    ],
                ],
            ],
        ],
        'report-issue' => [
            'title' => 'Report Issue',
            'eyebrow' => 'Support',
            'summary' => 'Tell us about broken playback, incorrect metadata, missing episodes, layout bugs, or unsafe content.',
            'badge' => 'Issue Queue',
            'sections' => [
                [
                    'heading' => 'What to include',
                    'items' => [
                        ['title' => 'Broken playback', 'body' => 'Include the title, episode or movie URL, selected server, browser, and device.'],
                        ['title' => 'Wrong information', 'body' => 'Include the title URL and the exact field that needs correction, such as year, poster, cast, or episode number.'],
                        ['title' => 'Site bug', 'body' => 'Include your screen size, browser, and a short description of where the interface failed.'],
                    ],
                ],
            ],
        ],
        'request-title' => [
            'title' => 'Request Title',
            'eyebrow' => 'Catalogue',
            'summary' => 'Request a movie, anime, or TV show for catalogue review and future import prioritization.',
            'badge' => 'Title Request',
            'sections' => [
                [
                    'heading' => 'Request details',
                    'items' => [
                        ['title' => 'Title name', 'body' => 'Send the official title and release year to avoid duplicate or incorrect matches.'],
                        ['title' => 'Media type', 'body' => 'Tell us whether it is a movie, TV show, anime series, special, or OVA.'],
                        ['title' => 'Region or language', 'body' => 'If the title has multiple versions, include country, language, or alternate title details.'],
                    ],
                ],
            ],
        ],
        'privacy-policy' => [
            'title' => 'Privacy Policy',
            'eyebrow' => 'Legal',
            'summary' => 'How Vexio handles basic usage data, browser storage, advertising signals, and support requests.',
            'badge' => 'Privacy',
            'sections' => [
                [
                    'heading' => 'Information we use',
                    'items' => [
                        ['title' => 'Usage signals', 'body' => 'We may use aggregate page activity to improve ranking, navigation, performance, and catalogue quality.'],
                        ['title' => 'Local storage', 'body' => 'Some interface choices, such as ad timing or dismissed banners, may be stored in your browser.'],
                        ['title' => 'Support messages', 'body' => 'Information you send through support channels is used to investigate and respond to the request.'],
                    ],
                ],
                [
                    'heading' => 'Your control',
                    'items' => [
                        ['title' => 'Browser controls', 'body' => 'You can clear cookies, local storage, or cache through your browser settings at any time.'],
                        ['title' => 'Sensitive data', 'body' => 'Do not send passwords, payment details, or private identification through support requests.'],
                    ],
                ],
            ],
        ],
        'terms-of-use' => [
            'title' => 'Terms of Use',
            'eyebrow' => 'Legal',
            'summary' => 'The basic rules for using Vexio responsibly and respecting rights holders, viewers, and the community.',
            'badge' => 'Terms',
            'sections' => [
                [
                    'heading' => 'Using Vexio',
                    'items' => [
                        ['title' => 'Personal use', 'body' => 'Vexio is intended for personal discovery and viewing. Automated scraping or abuse is not allowed.'],
                        ['title' => 'Respectful behavior', 'body' => 'Do not submit harmful, illegal, misleading, or abusive reports, requests, or comments.'],
                        ['title' => 'Service changes', 'body' => 'Features, catalogue entries, routes, and availability may change as the platform evolves.'],
                    ],
                ],
            ],
        ],
        'dmca' => [
            'title' => 'DMCA',
            'eyebrow' => 'Legal',
            'summary' => 'Rights holders can submit takedown notices or correction requests with the details needed for review.',
            'badge' => 'Rights',
            'sections' => [
                [
                    'heading' => 'Notice requirements',
                    'items' => [
                        ['title' => 'Identify the work', 'body' => 'Include the copyrighted work, the Vexio URL, and enough detail to locate the material.'],
                        ['title' => 'Authority and contact', 'body' => 'Include your legal name, organization if applicable, contact email, and statement of authority.'],
                        ['title' => 'Good-faith statement', 'body' => 'Include a good-faith belief that the disputed use is not authorized by the rights holder or law.'],
                    ],
                ],
            ],
        ],
        'advertise' => [
            'title' => 'Advertise',
            'eyebrow' => 'Partners',
            'summary' => 'Explore Vexio placements for entertainment, gaming, anime, and media campaigns across responsive surfaces.',
            'badge' => 'Media Kit',
            'sections' => [
                [
                    'heading' => 'Available placements',
                    'items' => [
                        ['title' => 'Mobile sticky banner', 'body' => 'A compact mobile placement visible across browsing surfaces with close control.'],
                        ['title' => 'Interstitial placement', 'body' => 'A timed interstitial format with interval handling so it does not appear on every refresh.'],
                        ['title' => 'Leaderboard and sidebar', 'body' => 'Desktop and content-adjacent units are available across archive and watch experiences.'],
                    ],
                ],
            ],
        ],
    ];

    public function __construct(private TemplateEngine $view)
    {
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
                'message' => 'The support page you requested could not be found.',
            ]), 404);
        }

        $page = $this->pages[$slug];

        return $response->html($this->view->render('frontend/support/page', 'layouts/frontend/paper', [
            ...$page,
            'slug' => $slug,
            'body_class' => 'paper-support-page',
        ]));
    }
}
