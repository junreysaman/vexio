<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$targets = [
    'src/App/views/frontend/forum/index.php',
    'src/App/views/frontend/forum/thread.php',
    'src/App/views/frontend/watch/movie.php',
    'src/App/views/frontend/watch/tvshow.php',
    'src/App/views/frontend/partials/skeleton-loader.php',
    'src/App/views/frontend/partials/share-modal.php',
    'src/App/views/admin/importer/index.php',
];

foreach ($targets as $relative) {
    $path = $root . '/' . $relative;
    $content = file_get_contents($path);
    $original = $content;

    // Remove styles section wrapper used by some views.
    $content = preg_replace(
        '/<\?= \$this->start\(\'styles\'\) \?>\s*<style>.*?<\/style>\s*<\?= \$this->end\(\) \?>\s*\n*/s',
        '',
        $content
    );

    $content = preg_replace(
        '/<\?= \$this->start\("styles"\) \?>\s*<style>.*?<\/style>\s*<\?= \$this->end\(\) \?>\s*\n*/s',
        '',
        $content
    );

    // Remove bare style blocks in partials.
    $content = preg_replace('/\s*<style>.*?<\/style>\s*/s', "\n", $content, 1);

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated {$relative}\n";
    } else {
        echo "No change {$relative}\n";
    }
}
