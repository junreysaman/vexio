<?php
$stats = !empty($siteStats) && is_array($siteStats) ? $siteStats : [];
$icons = [
    'Movies' => '<path d="M4 6h16v12H4z"/><path d="M8 6v12"/><path d="M16 6v12"/><path d="M4 10h4"/><path d="M16 10h4"/><path d="M4 14h4"/><path d="M16 14h4"/>',
    'TV Shows' => '<rect x="3" y="5" width="18" height="12" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/>',
    'Episodes' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6"/><path d="M9 13h6"/><path d="M9 17h4"/>',
    'Genres' => '<path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>',
];
?>

<?php if ($stats !== []): ?>
  <section id="site-stats" aria-label="Catalogue stats">
    <div class="container">
      <div class="site-stats-band">
        <div class="site-stats-copy">
          <span>Catalogue</span>
          <strong>Fresh picks across every shelf</strong>
        </div>
        <div class="site-stats-grid">
          <?php foreach ($stats as $stat): ?>
            <?php
              $label = (string) ($stat['label'] ?? '');
              $value = max(0, (int) ($stat['value'] ?? 0));
              $tone = preg_replace('/[^a-z]/', '', strtolower((string) ($stat['tone'] ?? 'accent'))) ?: 'accent';
              $path = $icons[$label] ?? $icons['Genres'];
            ?>
            <div class="site-stat site-stat-<?= htmlspecialchars($tone, ENT_QUOTES) ?>">
              <div class="site-stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $path ?></svg>
              </div>
              <div>
                <strong><?= htmlspecialchars(number_format($value), ENT_QUOTES) ?></strong>
                <span><?= htmlspecialchars($label, ENT_QUOTES) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>
