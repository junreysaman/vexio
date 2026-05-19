<?= $this->start('content') ?>
<?php
$pageTitle = (string) ($title ?? 'Support');
$pageEyebrow = (string) ($eyebrow ?? 'Support');
$pageSummary = (string) ($summary ?? '');
$pageBadge = (string) ($badge ?? 'Vexio');
$pageSections = is_array($sections ?? null) ? $sections : [];
?>

<main class="support-page">
  <section class="vex-page-hero">
    <div class="container">
      <div class="vex-page-hero-inner">
        <div class="arch-breadcrumb">
          <a href="/">Home</a>
          <span>/</span>
          <span><?= escape($pageEyebrow) ?></span>
          <span>/</span>
          <span style="color:var(--muted2);"><?= escape($pageTitle) ?></span>
        </div>
        <div class="vex-page-hero-top">
          <div>
            <p class="vex-page-kicker"><?= escape($pageEyebrow) ?></p>
            <h1 class="vex-page-title"><?= escape($pageTitle) ?></h1>
            <p class="vex-page-sub"><?= escape($pageSummary) ?></p>
          </div>
          <div class="vex-page-pill"><?= escape($pageBadge) ?></div>
        </div>
      </div>
    </div>
  </section>

  <section class="support-content">
    <div class="container">
      <div class="support-grid">
        <?php foreach ($pageSections as $section): ?>
          <article class="support-panel">
            <h2><?= escape((string) ($section['heading'] ?? 'Details')) ?></h2>
            <?php foreach (($section['items'] ?? []) as $item): ?>
              <div class="support-item">
                <h3><?= escape((string) ($item['title'] ?? 'Item')) ?></h3>
                <p><?= escape((string) ($item['body'] ?? '')) ?></p>
              </div>
            <?php endforeach; ?>
          </article>
        <?php endforeach; ?>

        <aside class="support-panel support-aside">
          <h2>Need a specific route?</h2>
          <p>Use the matching footer link for FAQ, contact, reports, title requests, privacy, terms, DMCA, or advertising information.</p>
          <div class="support-actions">
            <a href="/archive/trending">View Trending</a>
            <a href="/archive/browse">Browse Catalogue</a>
          </div>
        </aside>
      </div>
    </div>
  </section>
</main>
<?= $this->end() ?>
