<?php
$embedServers = is_array($episode['embed_servers'] ?? null) ? $episode['embed_servers'] : [];
$episodeViews = (int) ($episode['views'] ?? 0);
$showViews = (int) ($show['views'] ?? 0);
?>
<div class="server-bar">
  <div class="container">
    <div class="server-inner">
      <span class="server-label">Server</span>
      <div class="server-tabs">
        <?php foreach ($embedServers as $server): ?>
          <?php
            $serverName = (string) ($server['name'] ?? 'Server');
            $serverKey = (string) ($server['key'] ?? strtolower($serverName));
            $serverUrl = (string) ($server['url'] ?? '');
            $isDefault = !empty($server['default']);
          ?>
          <?php if ($serverUrl !== ''): ?>
            <button
              class="server-tab<?= $isDefault ? ' active' : '' ?>"
              type="button"
              onclick="selectServer(this,'<?= escape($serverKey) ?>')"
              data-server-key="<?= escape($serverKey) ?>"
              data-server-url="<?= escape($serverUrl) ?>"
              data-server-embed-url="<?= escape($serverUrl) ?>"
              data-embed-url="<?= escape($serverUrl) ?>"
              data-server-name="<?= escape($serverName) ?>"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"/></svg>
              <?= escape($serverName) ?> <?= $isDefault ? '<span class="server-live-dot" aria-hidden="true"></span>' : '' ?>
            </button>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <div class="server-divider"></div>
      <div class="server-stats" aria-label="Watch stats">
        <span><strong><?= number_format($episodeViews) ?></strong> episode views</span>
        <span><strong><?= number_format($showViews) ?></strong> show views</span>
      </div>
    </div>
  </div>
</div>
