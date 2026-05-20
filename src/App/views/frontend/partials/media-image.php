<?php
/**
 * Renders a responsive catalogue image with lazy-load skeleton.
 *
 * Required:
 *   array $media  — src, srcset, sizes, width, height (from MediaImage helper)
 *
 * Optional:
 *   string $alt
 *   string $loading — lazy|eager
 *   string $fetchpriority — high|low|auto
 *   string $class
 */
use App\Support\MediaImage;

$media = is_array($media ?? null) ? $media : [];
$src = trim((string) ($media['src'] ?? ''));
if ($src === '') {
    return;
}

$srcset = trim((string) ($media['srcset'] ?? ''));
$sizes = trim((string) ($media['sizes'] ?? ''));
$width = (int) ($media['width'] ?? 0);
$height = (int) ($media['height'] ?? 0);
$alt = (string) ($alt ?? '');
$loading = (string) ($loading ?? 'lazy');
$fetchpriority = trim((string) ($fetchpriority ?? ''));
$class = trim((string) ($class ?? ''));
$imgClass = trim('media-img__img ' . $class);
?>
<div class="media-img" data-media-img>
  <span class="media-img__skeleton" aria-hidden="true"></span>
  <img
    src="<?= escape($src) ?>"
    alt="<?= escape($alt) ?>"
    loading="<?= escape($loading) ?>"
    decoding="async"
    <?php if ($srcset !== ''): ?>srcset="<?= escape($srcset) ?>"<?php endif; ?>
    <?php if ($sizes !== ''): ?>sizes="<?= escape($sizes) ?>"<?php endif; ?>
    <?php if ($width > 0): ?>width="<?= $width ?>"<?php endif; ?>
    <?php if ($height > 0): ?>height="<?= $height ?>"<?php endif; ?>
    <?php if ($fetchpriority !== ''): ?>fetchpriority="<?= escape($fetchpriority) ?>"<?php endif; ?>
    class="<?= escape($imgClass) ?>"
  >
</div>
