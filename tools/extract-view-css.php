<?php

declare(strict_types=1);

$root = dirname(__DIR__);

function extractStyleBlock(string $path, ?int $startLine = null, ?int $endLine = null): string
{
    $lines = file($path);
    $in = false;
    $css = [];

    foreach ($lines as $i => $line) {
        $n = $i + 1;
        $trim = trim($line);

        if ($trim === '<style>') {
            $in = true;
            continue;
        }

        if ($trim === '</style>') {
            $in = false;
            continue;
        }

        if (!$in) {
            continue;
        }

        if ($startLine !== null && $n < $startLine) {
            continue;
        }

        if ($endLine !== null && $n > $endLine) {
            continue;
        }

        $css[] = rtrim($line, "\r\n");
    }

    return implode("\n", $css);
}

$frontendSections = [
    ['Skeleton loader', 'src/App/views/frontend/partials/skeleton-loader.php'],
    ['Share modal', 'src/App/views/frontend/partials/share-modal.php'],
    ['Forum index', 'src/App/views/frontend/forum/index.php'],
    ['Forum thread', 'src/App/views/frontend/forum/thread.php'],
    ['Archive browse', 'src/App/views/frontend/archive/browse/components/styles.php', 262, 941],
    ['Genre page', 'src/App/views/frontend/archive/genre-page/components/styles.php'],
    ['Watch legacy movie', 'src/App/views/frontend/watch/movie.php'],
    ['Watch legacy tvshow', 'src/App/views/frontend/watch/tvshow.php'],
];

$out = "/* Vexio global view styles — extracted from PHP templates. */\n\n";

foreach ($frontendSections as $section) {
    $label = $section[0];
    $file = $root . '/' . $section[1];
    $start = $section[2] ?? null;
    $end = $section[3] ?? null;
    $css = extractStyleBlock($file, $start, $end);
    $out .= "/* ===== {$label} ===== */\n{$css}\n\n";
}

$frontendPath = $root . '/public/assets/frontend/css/global-views.css';
file_put_contents($frontendPath, $out);

$importerCss = extractStyleBlock($root . '/src/App/views/admin/importer/index.php');
$backendPath = $root . '/public/assets/backend/css/global-views.css';
file_put_contents(
    $backendPath,
    "/* Vexio admin view styles — extracted from admin/importer/index.php */\n\n{$importerCss}\n"
);

echo 'Wrote ' . $frontendPath . ' (' . filesize($frontendPath) . " bytes)\n";
echo 'Wrote ' . $backendPath . ' (' . filesize($backendPath) . " bytes)\n";
