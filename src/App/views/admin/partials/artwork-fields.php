<?php
/**
 * TMDB artwork fields for admin content forms.
 *
 * Expected:
 *   array $artworkRow — row with poster_url, backdrop_url
 *   bool  $compact   — shorter layout for season/episode cards
 */
use App\Support\MediaImage;

$row = is_array($artworkRow ?? null) ? $artworkRow : [];
$posterBase = MediaImage::posterBaseUrl();
$backdropBase = MediaImage::backdropBaseUrl();
$posterValue = MediaImage::displayPosterUrl($row);
$backdropValue = MediaImage::displayBackdropUrl($row);
$pathHint = 'TMDB path (e.g. /abc123.jpg) or full URL';
?>
<div class="form-group">
    <label class="col-form-label s-12">POSTER (TMDB)</label>
    <input
        class="form-control r-0 light s-12"
        name="poster_url"
        type="text"
        value="<?= escape($posterValue) ?>"
        placeholder="<?= escape($posterBase . '/filename.jpg') ?>"
    >
    <small class="text-muted">Base: <?= escape($posterBase) ?> — <?= escape($pathHint) ?></small>
</div>
<div class="form-group">
    <label class="col-form-label s-12">BACKDROP (TMDB)</label>
    <input
        class="form-control r-0 light s-12"
        name="backdrop_url"
        type="text"
        value="<?= escape($backdropValue) ?>"
        placeholder="<?= escape($backdropBase . '/filename.jpg') ?>"
    >
    <small class="text-muted">Base: <?= escape($backdropBase) ?> — <?= escape($pathHint) ?></small>
</div>
