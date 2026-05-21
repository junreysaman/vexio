<?php
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');

/*
|--------------------------------------------------------------------------
| Detect Watch Route
|--------------------------------------------------------------------------
*/
$isWatchPage = str_starts_with($currentPath, 'watch/');

/*
|--------------------------------------------------------------------------
| Navigation Items
|--------------------------------------------------------------------------
*/
$navItems = [
    [
        'key' => 'home',
        'label' => 'Home',
        'href' => '/',
        'active' => $currentPath === '',
        'icon' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
    ],

    

    [
        'key' => 'browse',
        'label' => 'Browse',
        'href' => '/archive/browse',
        'active' => $currentPath === 'archive/browse',
        'icon' => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
    ],

    [
        'key' => 'genre',
        'label' => 'Genre',
        'href' => '/archive/genres',
        'active' => in_array($currentPath, ['archive/genres', 'genres'], true)
            || str_starts_with($currentPath, 'genre/'),
        'icon' => '<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>',
    ],

    [
        'key' => 'networks',
        'label' => 'Networks',
        'href' => '/archive/networks',
        'active' => in_array($currentPath, ['archive/networks', 'networks'], true)
            || str_starts_with($currentPath, 'network/'),
        'icon' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15 15 0 0 1 0 20"/><path d="M12 2a15 15 0 0 0 0 20"/>',
    ],

    [
        'key' => 'trending',
        'label' => 'Trending',
        'href' => '/archive/trending',
        'active' => $currentPath === 'archive/trending',
        'icon' => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hidden Watch Menu
    | Only visible on /watch/*
    |--------------------------------------------------------------------------
    */
    ...($isWatchPage ? [[
        'key' => 'watching',
        'label' => 'Watching',
        'href' => 'javascript:void(0)',
        'active' => true,
        'disabled' => true,
        'icon' => '<polygon points="5 3 19 12 5 21 5 3"/>',
    ]] : []),
];

$mobileNavItems = array_values(array_filter(
    $navItems,
    static fn (array $item): bool => in_array($item['key'], ['home', 'browse', 'genre', 'networks', 'trending', 'watching'], true)
));
?>

<!-- ===================================================== -->
<!-- TOP NAV -->
<!-- ===================================================== -->

<nav id="topnav">

    <a href="/" class="vx-logo">
        <img src="/brand/vexio-transparent.png" alt="Vexio" width="140" height="48" decoding="async">
    </a>

    <div class="nav-links">

        <?php foreach ($navItems as $item): ?>

            <a
                href="<?= escape($item['href']) ?>"
                class="nav-link
                    <?= $item['active'] ? 'active' : '' ?>
                    <?= !empty($item['disabled']) ? 'disabled' : '' ?>"
                data-nav="<?= escape($item['key']) ?>"
                <?= !empty($item['disabled']) ? 'aria-disabled="true"' : '' ?>
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <?= $item['icon'] ?>
                </svg>

                <?= escape($item['label']) ?>

            </a>

        <?php endforeach; ?>

    </div>

    <div class="nav-right">

        <div class="nav-search-bar" id="searchOpen">

            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>

            <span>Search TV shows, movies...</span>

            <kbd>Ctrl K</kbd>

        </div>
        <?php if ($currentUser): ?>
            <form action="/logout" method="POST" style="display: inline;">
                <input type="hidden" name="token" value="<?= escape($_csrfToken) ?>">
                <button type="submit" class="nav-sign-btn">
                    Sign Out
                </button>
            </form>
        <?php else: ?>
            <a href="/login" class="nav-sign-btn">
                Sign In
            </a>
        <?php endif; ?>

        <button
            class="mobile-search-btn"
            id="mobileSearchOpen"
            aria-label="Search"
        >

            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>

        </button>

    </div>

</nav>

<!-- ===================================================== -->
<!-- BOTTOM NAV -->
<!-- ===================================================== -->

<nav id="botnav">

    <?php foreach ($mobileNavItems as $item): ?>

        <a
            href="<?= escape($item['href']) ?>"
            class="bot-nav-item
                <?= $item['active'] ? 'active' : '' ?>
                <?= !empty($item['disabled']) ? 'disabled' : '' ?>"
            data-nav="<?= escape($item['key']) ?>"
            <?= !empty($item['disabled']) ? 'aria-disabled="true"' : '' ?>
        >

            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <?= $item['icon'] ?>
            </svg>

            <?= escape($item['label']) ?>

            <?php if ($item['key'] === 'trending'): ?>
                <span class="bot-nav-dot"></span>
            <?php endif; ?>

        </a>

    <?php endforeach; ?>

</nav>

<!-- ===================================================== -->
<!-- OPTIONAL CSS -->
<!-- ===================================================== -->
