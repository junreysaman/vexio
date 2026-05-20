<?php

declare(strict_types=1);

/**
 * Static content for public support and legal routes (footer links).
 *
 * Item fields:
 * - title (string)
 * - body (string, optional)
 * - paragraphs (list of strings, optional)
 * - bullets (list of strings, optional)
 *
 * @return array<string, array<string, mixed>>
 */
return [
    'faq' => [
        'title' => 'FAQ',
        'eyebrow' => 'Support',
        'summary' => 'Answers to common questions about finding titles, watching, reporting problems, and how VEXIO works day to day.',
        'badge' => 'Help Center',
        'meta' => ['last_updated' => 'May 20, 2026'],
        'sidebar' => [
            'heading' => 'Still stuck?',
            'body' => 'If your answer is not here, use Contact for general questions or Report Issue for playback and bugs. Title gaps are tracked through Request Title.',
            'actions' => [
                ['label' => 'Contact us', 'href' => '/contact'],
                ['label' => 'Report an issue', 'href' => '/report-issue'],
            ],
        ],
        'sections' => [
            [
                'heading' => 'Getting started',
                'items' => [
                    [
                        'title' => 'Do I need an account?',
                        'body' => 'No. You can browse the catalogue and start watching without registering. Some community features may require an account if we enable them in the future.',
                    ],
                    [
                        'title' => 'Is VEXIO free?',
                        'body' => 'The site is supported by advertising. You may see display or video ads on browse and watch pages depending on your region and campaign load.',
                    ],
                    [
                        'title' => 'Which browsers work best?',
                        'bullets' => [
                            'Current versions of Chrome, Firefox, Safari, and Edge on desktop.',
                            'Mobile: recent iOS Safari and Android Chrome. Enable JavaScript; disable aggressive blockers if streams fail to start.',
                        ],
                    ],
                ],
            ],
            [
                'heading' => 'Watching and playback',
                'items' => [
                    [
                        'title' => 'The player buffers or stops',
                        'paragraphs' => [
                            'Try another server or quality option when available. Close other heavy tabs, pause downloads on the same connection, and switch from cellular to Wi‑Fi when possible.',
                            'If only one title fails, use Report Issue with the exact URL, server name, and device so we can trace a pattern.',
                        ],
                    ],
                    [
                        'title' => 'Audio, subtitles, or wrong episode',
                        'body' => 'Note the track you selected and what you heard or saw. For other metadata problems (wrong year, cast, or artwork), include a screenshot and the public title page URL.',
                    ],
                    [
                        'title' => 'Downloads and offline viewing',
                        'body' => 'VEXIO is built for streaming in the browser. We do not provide official offline download files; respect the terms that apply in your country.',
                    ],
                ],
            ],
            [
                'heading' => 'Catalogue and discovery',
                'items' => [
                    [
                        'title' => 'Why is a show or movie missing?',
                        'body' => 'Licensing, source availability, and quality checks all affect what appears. You can submit Request Title with the official name and year—we use requests to prioritize imports and fixes.',
                    ],
                    [
                        'title' => 'How does Trending work?',
                        'body' => 'Trending blends recent viewing activity with catalogue quality signals so busy lists stay fresh. It is not a paid placement.',
                    ],
                    [
                        'title' => 'Genres look empty or outdated',
                        'body' => 'Try Browse or Search. If a genre page should list items but does not, report it as a site issue with the genre URL.',
                    ],
                ],
            ],
            [
                'heading' => 'Ads, privacy, and safety',
                'items' => [
                    [
                        'title' => 'Ads feel intrusive or broken',
                        'body' => 'Tell us the page URL, approximate time, and whether it was mobile or desktop. We work with networks to cap frequency and remove broken creatives when we can identify them.',
                    ],
                    [
                        'title' => 'Where is your Privacy Policy?',
                        'body' => 'See Privacy Policy for cookies, analytics, and what we collect when you use support forms or email. Terms of Use covers acceptable use.',
                    ],
                    [
                        'title' => 'Someone posted unsafe or illegal content',
                        'body' => 'Use Report Issue and choose the closest category, or email our support address with links and screenshots. We take abuse reports seriously.',
                    ],
                ],
            ],
        ],
    ],

    'contact' => [
        'title' => 'Contact',
        'eyebrow' => 'Support',
        'summary' => 'How to reach VEXIO for help, feedback, press, and partnerships. The more detail you send, the faster we can route your message.',
        'badge' => 'Response desk',
        'meta' => ['last_updated' => 'May 20, 2026'],
        'sidebar' => [
            'heading' => 'Quick links',
            'body' => 'Playback and broken pages: Report Issue. Missing films or series: Request Title. Legal and copyright: DMCA.',
            'actions' => [
                ['label' => 'Report Issue', 'href' => '/report-issue'],
                ['label' => 'DMCA', 'href' => '/dmca'],
            ],
        ],
        'sections' => [
            [
                'heading' => 'Email (recommended)',
                'items' => [
                    [
                        'title' => 'Support & general questions',
                        'paragraphs' => [
                            'Use the address published on this page once your administrator sets APP_SUPPORT_EMAIL in the server environment. That keeps a single inbox for triage.',
                            'Subject line tips: include one keyword like "Playback", "Wrong metadata", "Ad bug", or "Account" plus the title name if relevant.',
                        ],
                        'bullets' => [
                            'Paste the full page URL (not only the site name).',
                            'List browser, OS, and whether you are on phone or desktop.',
                            'Screenshots help for layout issues; a short screen recording helps for player glitches.',
                        ],
                    ],
                    [
                        'title' => 'Legal, copyright, and law enforcement',
                        'body' => 'Formal copyright notices must follow the process on our DMCA page so they contain the required statements and identification. General legal correspondence should go to the legal contact configured in the environment (APP_LEGAL_EMAIL) once set by your team.',
                    ],
                ],
            ],
            [
                'heading' => 'What we can (and cannot) do',
                'items' => [
                    [
                        'title' => 'Typical response time',
                        'body' => 'We read every message, but volume varies. Many playback reports are grouped and prioritized by impact. You may not receive a personal reply for every duplicate report.',
                    ],
                    [
                        'title' => 'No account recovery by email alone',
                        'body' => 'If you did not register an account, there is no password to reset. If we add accounts later, recovery will be documented in the FAQ.',
                    ],
                    [
                        'title' => 'Advertising and partnerships',
                        'body' => 'For media buys, sponsorships, and network partnerships, use the Advertise page so the request includes format and budget context.',
                    ],
                ],
            ],
        ],
    ],

    'report-issue' => [
        'title' => 'Report Issue',
        'eyebrow' => 'Support',
        'summary' => 'Use this checklist when playback fails, metadata is wrong, the layout breaks, or you need to flag abusive or dangerous content.',
        'badge' => 'Issue queue',
        'sidebar' => [
            'heading' => 'Prefer email?',
            'body' => 'You can send the same information to your configured support address. Include "Report" in the subject so it lands in the right queue.',
            'actions' => [
                ['label' => 'Request a title', 'href' => '/request-title'],
                ['label' => 'Back to FAQ', 'href' => '/faq'],
            ],
        ],
        'sections' => [
            [
                'heading' => 'Before you write',
                'items' => [
                    [
                        'title' => 'Try quick fixes',
                        'bullets' => [
                            'Hard refresh the page (Ctrl+F5 or Cmd+Shift+R).',
                            'Try another browser or private window to rule out extensions.',
                            'Toggle another server or player option when the UI offers it.',
                        ],
                    ],
                    [
                        'title' => 'One report per distinct bug',
                        'body' => 'If a whole category of pages fails, say so once with examples. If a single episode is broken, send that URL only—duplicates slow triage.',
                    ],
                ],
            ],
            [
                'heading' => 'What to include in your report',
                'items' => [
                    [
                        'title' => 'Broken playback',
                        'bullets' => [
                            'Exact watch page URL.',
                            'Movie vs episode (season and episode number for series).',
                            'Browser + version, OS, phone model if mobile.',
                            'Error text on screen, if any; approximate local time (with timezone).',
                        ],
                    ],
                    [
                        'title' => 'Wrong metadata or missing episode',
                        'paragraphs' => [
                            'Link the VEXIO title page. List what is incorrect: release year, runtime, cast, artwork, episode order, etc., and the correct information with a trustworthy reference if you have one (official site or TMDb-style ID).',
                        ],
                    ],
                    [
                        'title' => 'Site or layout bug',
                        'body' => 'Describe the expected vs actual behavior. Note viewport width or attach a screenshot. Tell us if it happens only when logged in (if applicable).',
                    ],
                    [
                        'title' => 'Safety, harassment, or illegal content',
                        'body' => 'Do not include personal data unrelated to the report. Provide URLs, time, and a factual description. We may preserve logs in line with our Privacy Policy where permitted.',
                    ],
                ],
            ],
            [
                'heading' => 'After you send',
                'items' => [
                    [
                        'title' => 'What happens next',
                        'body' => 'Reports are grouped by type. Critical outages and widespread player failures are prioritized. We may fix the catalogue silently without replying to every sender.',
                    ],
                ],
            ],
        ],
    ],

    'request-title' => [
        'title' => 'Request Title',
        'eyebrow' => 'Catalogue',
        'summary' => 'Ask us to add or prioritize a movie, series, or special. Accurate titles and years help us match the right work and avoid duplicates.',
        'badge' => 'Catalogue',
        'sidebar' => [
            'heading' => 'Check first',
            'body' => 'Search the site and browse genres—many requests already exist under alternate titles or regional names.',
            'actions' => [
                ['label' => 'Browse catalogue', 'href' => '/archive/browse'],
                ['label' => 'Trending', 'href' => '/archive/trending'],
            ],
        ],
        'sections' => [
            [
                'heading' => 'How to submit a strong request',
                'items' => [
                    [
                        'title' => 'Required details',
                        'bullets' => [
                            'Official title as shown on posters or the primary database entry.',
                            'Release year (for film) or first air year (for series).',
                            'Type: movie, TV series, anime, special, or documentary.',
                            'Language or country of origin if ambiguous.',
                        ],
                    ],
                    [
                        'title' => 'Optional but helpful',
                        'bullets' => [
                            'TMDb or IMDb ID.',
                            'Alternative titles (AKA) used in your region.',
                            'Why it matters to you—popularity spikes help us schedule imports.',
                        ],
                    ],
                ],
            ],
            [
                'heading' => 'Expectations',
                'items' => [
                    [
                        'title' => 'We cannot promise every title',
                        'body' => 'Availability depends on sources, technical matching, and policy. Requesting does not create an entitlement; it informs our backlog.',
                    ],
                    [
                        'title' => 'Duplicates',
                        'body' => 'Requests for the same property under different spellings may be merged. Use the official title to keep the queue clean.',
                    ],
                    [
                        'title' => 'Exclusive or unreleased content',
                        'body' => 'We do not solicit or host material that is not generally available through legitimate publication. Do not ask for leaked or stolen uploads.',
                    ],
                ],
            ],
        ],
    ],

    'privacy-policy' => [
        'title' => 'Privacy Policy',
        'eyebrow' => 'Legal',
        'summary' => 'This policy explains what information is processed when you use VEXIO, how ads and analytics may work, and the choices available to you in the browser.',
        'badge' => 'Privacy',
        'meta' => ['last_updated' => 'May 20, 2026'],
        'sidebar' => [
            'heading' => 'Related',
            'body' => 'Terms of Use describe rules for using the service. For ad partners, see Advertise. Questions can go through Contact once your support email is configured.',
            'actions' => [
                ['label' => 'Terms of Use', 'href' => '/terms-of-use'],
                ['label' => 'Cookie hints', 'href' => '/faq'],
            ],
        ],
        'sections' => [
            [
                'heading' => 'Who we are',
                'items' => [
                    [
                        'title' => 'Service',
                        'paragraphs' => [
                            'VEXIO operates a streaming discovery and playback website (the "Service"). The operator is whoever publishes the site at the domain you are visiting. Contact details should be supplied by that operator via the environment configuration for support and legal email.',
                            'This policy is meant for a general, international audience. Local laws where you live may give you additional rights.',
                        ],
                    ],
                ],
            ],
            [
                'heading' => 'Information we may process',
                'items' => [
                    [
                        'title' => 'Technical and usage data',
                        'bullets' => [
                            'Server and CDN logs: IP address, approximate location, user agent, timestamps, requested URL, and HTTP status.',
                            'In-application events: pages visited, search queries, player interactions, and errors—often in aggregated or pseudonymous form.',
                            'Security signals: rate limits, abuse patterns, and basic device fingerprints needed to block bots.',
                        ],
                    ],
                    [
                        'title' => 'Information you provide',
                        'body' => 'If you email us or use a form, we process the contents of your message and basic metadata (address, time) to respond and keep records of the inquiry where appropriate.',
                    ],
                    [
                        'title' => 'Cookies and similar storage',
                        'paragraphs' => [
                            'We or partners may set cookies, local storage, or similar technologies to remember preferences, measure ad delivery, cap frequency, and reduce fraud.',
                            'You can delete or block cookies through browser settings. Blocking some cookies may break login, personalization, or ad measurement features.',
                        ],
                    ],
                ],
            ],
            [
                'heading' => 'Advertising',
                'items' => [
                    [
                        'title' => 'Third-party ad partners',
                        'body' => 'Ads may be served by networks that operate their own privacy policies. They may collect or receive information as described in their policies and industry frameworks (for example IAB TCF where applicable). We choose partners that fit entertainment inventory, but we do not control their data practices.',
                    ],
                ],
            ],
            [
                'heading' => 'Retention and security',
                'items' => [
                    [
                        'title' => 'How long we keep data',
                        'body' => 'Logs are kept only as long as needed for operations, security, and legal compliance, then deleted or aggregated. Support emails may be retained longer if required to document an issue or legal request.',
                    ],
                    [
                        'title' => 'Security',
                        'body' => 'We use reasonable technical and organizational measures to protect the Service. No method of transmission over the Internet is completely secure.',
                    ],
                ],
            ],
            [
                'heading' => 'Your choices and rights',
                'items' => [
                    [
                        'title' => 'Access, correction, deletion',
                        'body' => 'Depending on your region, you may have rights to access, correct, or delete personal data we hold, or to object to certain processing. Contact us with your jurisdiction and request; we may need to verify identity before fulfilling sensitive requests.',
                    ],
                    [
                        'title' => 'Do not track',
                        'body' => 'There is no universal standard for browser Do Not Track signals. We treat privacy controls primarily through consent frameworks where required and through your browser cookie settings.',
                    ],
                ],
            ],
            [
                'heading' => 'Children',
                'items' => [
                    [
                        'title' => 'Age',
                        'body' => 'The Service is not directed at children under 13 (or a higher age required locally). We do not knowingly collect personal information from children for marketing. If you believe we have done so, contact us so we can delete it.',
                    ],
                ],
            ],
            [
                'heading' => 'Changes',
                'items' => [
                    [
                        'title' => 'Updates to this policy',
                        'body' => 'We may revise this page from time to time. Material changes will be noted by updating the "Last updated" date. Continued use after changes means you accept the revised policy.',
                    ],
                ],
            ],
        ],
    ],

    'terms-of-use' => [
        'title' => 'Terms of Use',
        'eyebrow' => 'Legal',
        'summary' => 'Rules for accessing and using VEXIO. By using the site, you agree to these terms and to applicable laws in your country.',
        'badge' => 'Terms',
        'meta' => ['last_updated' => 'May 20, 2026'],
        'sidebar' => [
            'heading' => 'Related policies',
            'body' => 'Privacy Policy explains data practices. Rights holders should review the DMCA page before sending notices.',
            'actions' => [
                ['label' => 'Privacy Policy', 'href' => '/privacy-policy'],
                ['label' => 'DMCA', 'href' => '/dmca'],
            ],
        ],
        'sections' => [
            [
                'heading' => 'Acceptance',
                'items' => [
                    [
                        'title' => 'Agreement',
                        'body' => 'These Terms of Use ("Terms") govern your access to VEXIO ("we", "us", "our") and the streaming and related features we provide. If you do not agree, do not use the Service.',
                    ],
                    [
                        'title' => 'Changes',
                        'body' => 'We may modify the Terms or the Service at any time. We will update this page and adjust the "Last updated" date when we do. Your continued use after the effective date constitutes acceptance.',
                    ],
                ],
            ],
            [
                'heading' => 'Using the Service',
                'items' => [
                    [
                        'title' => 'License to you',
                        'body' => 'We grant you a personal, non-exclusive, non-transferable, revocable license to access the Service for private, non-commercial viewing in line with these Terms and with the law that applies to you.',
                    ],
                    [
                        'title' => 'Accounts',
                        'body' => 'Where registration exists, you must provide accurate information and protect your credentials. You are responsible for activity under your account until you notify us of compromise.',
                    ],
                    [
                        'title' => 'Restrictions',
                        'bullets' => [
                            'No scraping, automated harvesting, or attempts to bypass rate limits unless we give written permission.',
                            'No interference with servers, players, ads, security, or other users.',
                            'No uploading malware, unlawful material, or content intended to harass or defraud.',
                            'No use of the Service to infringe intellectual property or to violate export or sanctions rules.',
                        ],
                    ],
                ],
            ],
            [
                'heading' => 'Content and intellectual property',
                'items' => [
                    [
                        'title' => 'Third-party content',
                        'body' => 'We do not host or store video files on our infrastructure. Playback is supplied by independent third-party streaming platforms or embedded players; we provide discovery, links, and navigation only. Videos, artwork, descriptions, and metadata may also be sourced from public databases. We do not claim ownership of third-party works and will respond to valid rights-holder notices as described on the DMCA page.',
                    ],
                    [
                        'title' => 'Our brand',
                        'body' => 'VEXIO names, logos, and the site design are protected. You may not use them without prior permission except as allowed by law.',
                    ],
                ],
            ],
            [
                'heading' => 'Disclaimers',
                'items' => [
                    [
                        'title' => 'As-is basis',
                        'body' => 'The Service is provided "as is" and "as available". We disclaim implied warranties of merchantability, fitness for a particular purpose, and non-infringement to the fullest extent permitted by law.',
                    ],
                    [
                        'title' => 'Availability',
                        'body' => 'We do not guarantee uninterrupted access, error-free streaming, or that any title will remain on the Service. Features may change or retire without notice.',
                    ],
                ],
            ],
            [
                'heading' => 'Limitation of liability',
                'items' => [
                    [
                        'title' => 'Cap',
                        'body' => 'To the maximum extent permitted by law, we and our affiliates are not liable for indirect, incidental, special, consequential, or punitive damages, or for loss of profits, data, or goodwill. Our aggregate liability for any claim arising from the Service is limited to the greater of fifty US dollars or what you paid us in the last twelve months for the Service (which may be zero for ad-supported access).',
                    ],
                ],
            ],
            [
                'heading' => 'Indemnity',
                'items' => [
                    [
                        'title' => 'Your responsibility',
                        'body' => 'You will defend and indemnify us against claims, damages, and expenses (including reasonable attorneys\' fees) arising from your misuse of the Service, your content, or your violation of these Terms or applicable law, except to the extent caused by our willful misconduct.',
                    ],
                ],
            ],
            [
                'heading' => 'Termination and law',
                'items' => [
                    [
                        'title' => 'Suspension',
                        'body' => 'We may suspend or terminate access for conduct that risks the Service, other users, or legal compliance.',
                    ],
                    [
                        'title' => 'Governing law',
                        'body' => 'Unless mandatory local law requires otherwise, these Terms are governed by the laws of the jurisdiction where the site operator is organized, without regard to conflict-of-law rules. Courts in that jurisdiction have exclusive venue, subject to non-waivable consumer protections where you live.',
                    ],
                ],
            ],
        ],
    ],

    'dmca' => [
        'title' => 'DMCA',
        'eyebrow' => 'Legal',
        'summary' => 'Procedure for copyright owners and agents to submit takedown notices regarding material accessible through VEXIO. Include complete information so we can act promptly.',
        'badge' => 'Rights',
        'meta' => ['last_updated' => 'May 20, 2026'],
        'sidebar' => [
            'heading' => 'Counter-notices',
            'body' => 'If material was removed in error, U.S. law allows counter-notification in appropriate cases. Consult qualified counsel; we cannot give legal advice.',
            'actions' => [
                ['label' => 'Terms of Use', 'href' => '/terms-of-use'],
                ['label' => 'Privacy Policy', 'href' => '/privacy-policy'],
            ],
        ],
        'sections' => [
            [
                'heading' => 'Designated agent',
                'items' => [
                    [
                        'title' => 'Where to send notices',
                        'paragraphs' => [
                            'Email is preferred for speed. Your site administrator should set APP_LEGAL_EMAIL in the environment to the mailbox monitored for DMCA and legal notices. Include "DMCA Notice" in the subject line.',
                            'If you must send postal mail, use the legal address supplied by the operator of this domain. Do not send notices to unrelated personal inboxes.',
                        ],
                    ],
                ],
            ],
            [
                'heading' => 'Notice requirements (17 U.S.C. §512(c)(3))',
                'items' => [
                    [
                        'title' => 'Identification of the work',
                        'body' => 'Clearly identify the copyrighted work claimed to have been infringed. If many works are listed on one notice, provide a representative list.',
                    ],
                    [
                        'title' => 'Identification of the material',
                        'body' => 'Provide information reasonably sufficient to locate the material on our Service—ideally the full VEXIO URL and a description of where it appears.',
                    ],
                    [
                        'title' => 'Contact information',
                        'body' => 'Include your name, mailing address, telephone number, and email address so we or your counterparty can reach you.',
                    ],
                    [
                        'title' => 'Good faith statements',
                        'bullets' => [
                            'A statement that you believe the use is not authorized by the owner, its agent, or the law.',
                            'A statement, under penalty of perjury, that your notice is accurate and that you are authorized to act on behalf of the copyright owner.',
                        ],
                    ],
                    [
                        'title' => 'Signature',
                        'body' => 'Provide a physical or electronic signature of the person authorized to act on behalf of the owner.',
                    ],
                ],
            ],
            [
                'heading' => 'Repeat infringers',
                'items' => [
                    [
                        'title' => 'Policy',
                        'body' => 'We may terminate accounts or technical access for users who are repeat infringers in appropriate circumstances, where account systems exist.',
                    ],
                ],
            ],
            [
                'heading' => 'Misrepresentations',
                'items' => [
                    [
                        'title' => 'Liability',
                        'body' => 'Under U.S. law, any person who knowingly materially misrepresents that material is infringing—or that material was removed by mistake—may be liable for damages. Seek legal advice if you are unsure.',
                    ],
                ],
            ],
        ],
    ],

    'advertise' => [
        'title' => 'Advertise',
        'eyebrow' => 'Partners',
        'summary' => 'Reach streaming and anime audiences across mobile and desktop. Tell us your KPIs, flight dates, and markets so we can recommend placements.',
        'badge' => 'Media',
        'meta' => ['last_updated' => 'May 20, 2026'],
        'sidebar' => [
            'heading' => 'First contact',
            'body' => 'Email your configured support or business address with "Ad inquiry", target country, budget band, and creative formats. We reply to qualified requests when capacity allows.',
            'actions' => [
                ['label' => 'Contact', 'href' => '/contact'],
                ['label' => 'FAQ', 'href' => '/faq'],
            ],
        ],
        'sections' => [
            [
                'heading' => 'Audience',
                'items' => [
                    [
                        'title' => 'Who visits VEXIO',
                        'paragraphs' => [
                            'Viewers come for movies, series, and genre browsing. Traffic mixes mobile and desktop with spikes around new season drops and weekend viewing.',
                            'We do not sell guaranteed demographic bundles unless disclosed in an insertion order. Use third-party verification where your policy requires it.',
                        ],
                    ],
                ],
            ],
            [
                'heading' => 'Formats we typically run',
                'items' => [
                    [
                        'title' => 'Display',
                        'bullets' => [
                            'Mobile anchor or sticky placements with safe close controls.',
                            'Desktop leaderboard and content-adjacent rectangles on browse and watch flows.',
                        ],
                    ],
                    [
                        'title' => 'Full-screen or interstitial',
                        'body' => 'Time-capped interstitials may appear between navigations. Frequency caps are configured to reduce fatigue and accidental clicks.',
                    ],
                    [
                        'title' => 'Video',
                        'body' => 'Pre-roll or mid-roll on supported players depends on inventory and partner tags. VAST/VPAID compatibility varies by integration—ask for the current spec sheet.',
                    ],
                ],
            ],
            [
                'heading' => 'Brand safety & policy',
                'items' => [
                    [
                        'title' => 'Controls',
                        'body' => 'Share block lists and category exclusions. We aim to keep adult, extreme violence, and illegal promotions off premium packages, subject to network capabilities.',
                    ],
                    [
                        'title' => 'Prohibited ads',
                        'bullets' => [
                            'Malware, phishing, or deceptive "download" buttons.',
                            'Counterfeit goods, unlicensed pharmaceuticals, or illegal gambling where restricted.',
                            'Hate or harassment targeting protected groups.',
                        ],
                    ],
                ],
            ],
            [
                'heading' => 'Measurement and billing',
                'items' => [
                    [
                        'title' => 'Reporting',
                        'body' => 'Campaign reports usually include impressions, clicks, and video quartiles when applicable. Third-party tags may be supported under a signed agreement.',
                    ],
                    [
                        'title' => 'Contracts',
                        'body' => 'Larger buys run under an insertion order or network agreement with payment terms, makegoods, and liability caps. We do not guarantee fixed viewability or completion rates unless written into the IO.',
                    ],
                ],
            ],
        ],
    ],
];
