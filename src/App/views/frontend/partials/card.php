<?php
/**
 * Shared media card partial — trend-card design used across the whole site.
 *
 * Required variables:
 *   string       $cardTitle
 *   string       $cardPoster     — URL or '' for placeholder
 *   string       $cardWatchUrl
 *
 * Optional variables:
 *   string       $cardLabel      — top-left pill: rank "#2" on trending, type "Movie"/"TV Show" elsewhere
 *   string       $cardBadge      — top-right pill text: 'NEW' | 'HOT' | 'TOP' | ''
 *   string       $cardBadgeClass — 'new' | 'hot' | 'top' | 'rise'
 *   float|null   $cardRating
 *   string       $cardYear
 *   string       $cardDataAttrs  — pre-built data-* attribute string for JS (already escaped)
 */
$cardTitle      = (string) ($cardTitle ?? 'Untitled');
$cardPoster     = (string) ($cardPoster ?? '');
$cardWatchUrl   = (string) ($cardWatchUrl ?? '#');
$cardLabel      = (string) ($cardLabel ?? '');
$cardBadge      = (string) ($cardBadge ?? '');
$cardBadgeClass = (string) ($cardBadgeClass ?? 'new');
$cardRating     = isset($cardRating) && is_numeric($cardRating) ? (float) $cardRating : null;
$cardYear       = (string) ($cardYear ?? '');
$cardDataAttrs  = (string) ($cardDataAttrs ?? '');

$safeUrl  = $cardWatchUrl !== '' && $cardWatchUrl !== '#' ? $cardWatchUrl : '#';
$noLink   = $safeUrl === '#' ? ' onclick="event.preventDefault();showToast(\'Watch unavailable\')"' : '';
$initials = strtoupper(substr(preg_replace('/[^a-z0-9]+/i', '', $cardTitle) ?: 'VX', 0, 3));
?>
<a class="trend-card" href="<?= escape($safeUrl) ?>"<?= $noLink ?> <?= $cardDataAttrs ?>>
  <?php if ($cardPoster !== ''): ?>
    <div class="tc-bg" style="background-image:url('<?= escape($cardPoster) ?>');"></div>
  <?php else: ?>
    <div class="tc-ph"><?= escape($initials) ?></div>
  <?php endif; ?>
  <div class="tc-gradient"></div>

  <?php if ($cardLabel !== ''): ?>
    <div class="tc-rank"><?= escape($cardLabel) ?></div>
  <?php endif; ?>

  <?php if ($cardBadge !== ''): ?>
    <div class="tc-badge <?= escape($cardBadgeClass) ?>"><?= escape($cardBadge) ?></div>
  <?php endif; ?>

  <div class="tc-overlay">
    <div class="tc-play-ring">
      <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
    </div>
  </div>

  <div class="tc-content">
    <div class="tc-title"><?= escape($cardTitle) ?></div>
    <div class="tc-meta">
      <?php if ($cardRating !== null): ?>
        <strong><?= escape(number_format($cardRating, 1)) ?></strong>
        <?php if ($cardYear !== ''): ?><span class="dot">·</span><?php endif; ?>
      <?php endif; ?>
      <?php if ($cardYear !== ''): ?>
        <span><?= escape($cardYear) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="tc-hover-strip"></div>
</a>
