<?= $this->start('content') ?>
<?php
$pageTitle = (string) ($title ?? 'Support');
$pageEyebrow = (string) ($eyebrow ?? 'Support');
$pageSummary = (string) ($summary ?? '');
$pageBadge = (string) ($badge ?? 'VEXIO');
$pageSections = is_array($sections ?? null) ? $sections : [];
$pageMeta = is_array($meta ?? null) ? $meta : [];
$supportEmail = trim((string) ($supportEmail ?? ''));
$legalEmail = trim((string) ($legalEmail ?? ''));
$sidebar = is_array($sidebar ?? null) ? $sidebar : [];
$sidebarHeading = (string) ($sidebar['heading'] ?? 'Need something else?');
$sidebarBody = (string) ($sidebar['body'] ?? '');
$sidebarActions = is_array($sidebar['actions'] ?? null) ? $sidebar['actions'] : [];
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
            <?php if (!empty($pageMeta['last_updated'])): ?>
              <p class="support-meta">Last updated <?= escape((string) $pageMeta['last_updated']) ?></p>
            <?php endif; ?>
          </div>
          <div class="vex-page-pill"><?= escape($pageBadge) ?></div>
        </div>
      </div>
    </div>
  </section>

  <section class="support-contact-strip">
    <div class="container">
      <div class="support-contact-strip-inner">
        <?php if ($supportEmail !== ''): ?>
          <div class="support-contact-item">
            <span class="support-contact-label">Support</span>
            <a href="mailto:<?= escape($supportEmail) ?>"><?= escape($supportEmail) ?></a>
          </div>
        <?php endif; ?>
        <?php if ($legalEmail !== ''): ?>
          <div class="support-contact-item">
            <span class="support-contact-label">Legal / DMCA</span>
            <a href="mailto:<?= escape($legalEmail) ?>"><?= escape($legalEmail) ?></a>
          </div>
        <?php endif; ?>
        <?php if ($supportEmail === '' && $legalEmail === ''): ?>
          <p class="support-contact-hint">Site operator: add <code>APP_SUPPORT_EMAIL</code> and <code>APP_LEGAL_EMAIL</code> to your <code>.env</code> file to display public contact addresses on these pages.</p>
        <?php elseif ($supportEmail === ''): ?>
          <p class="support-contact-hint">Add <code>APP_SUPPORT_EMAIL</code> to <code>.env</code> to show a general support address.</p>
        <?php elseif ($legalEmail === ''): ?>
          <p class="support-contact-hint">Add <code>APP_LEGAL_EMAIL</code> to <code>.env</code> for copyright and formal legal notices.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="support-content">
    <div class="container">
      <div class="support-grid">
        <div class="support-main-panels">
          <?php foreach ($pageSections as $section): ?>
            <article class="support-panel">
              <h2><?= escape((string) ($section['heading'] ?? 'Details')) ?></h2>
              <?php foreach (($section['items'] ?? []) as $item): ?>
                <div class="support-item">
                  <h3><?= escape((string) ($item['title'] ?? 'Item')) ?></h3>
                  <?php
                  $paragraphs = $item['paragraphs'] ?? null;
                  if (is_array($paragraphs) && $paragraphs !== []) {
                      foreach ($paragraphs as $para) {
                          echo '<p>' . escape((string) $para) . '</p>';
                      }
                  } elseif (!empty($item['body'])) {
                      echo '<p>' . escape((string) $item['body']) . '</p>';
                  }
                  $bullets = $item['bullets'] ?? null;
                  if (is_array($bullets) && $bullets !== []) {
                      echo '<ul class="support-bullets">';
                      foreach ($bullets as $b) {
                          echo '<li>' . escape((string) $b) . '</li>';
                      }
                      echo '</ul>';
                  }
                  ?>
                </div>
              <?php endforeach; ?>
            </article>
          <?php endforeach; ?>
        </div>

        <aside class="support-panel support-aside">
          <h2><?= escape($sidebarHeading) ?></h2>
          <?php if ($sidebarBody !== ''): ?>
            <p><?= escape($sidebarBody) ?></p>
          <?php endif; ?>
          <?php if ($sidebarActions !== []): ?>
            <div class="support-actions">
              <?php foreach ($sidebarActions as $action): ?>
                <?php
                $label = (string) ($action['label'] ?? '');
                $href = (string) ($action['href'] ?? '#');
                if ($label === '') {
                    continue;
                }
                ?>
                <a href="<?= escape($href) ?>"><?= escape($label) ?></a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </aside>
      </div>
    </div>
  </section>
</main>
<?= $this->end() ?>
