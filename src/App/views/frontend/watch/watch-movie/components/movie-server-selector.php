<?php
$servers = $item['embedServers'] ?? $item['embed_servers'] ?? [];
$servers = is_array($servers) ? $servers : [];
?>
<!-- SERVER / LANGUAGE SELECTOR -->
      <div class="server-bar">
        <div class="container">
          <div class="server-inner">
            <span class="server-label">Server</span>
            <div class="server-tabs">
              <?php foreach ($servers as $idx => $server): ?>
                <?php
                $name = (string) ($server['name'] ?? 'Server');
                $url = (string) ($server['url'] ?? '');
                if ($url === '') {
                  continue;
                }
                ?>
                <button
                  class="server-tab<?= $idx === 0 ? ' active' : '' ?>"
                  type="button"
                  data-embed-url="<?= escape($url) ?>"
                  data-server-name="<?= escape($name) ?>"
                  onclick="selectServer(this)"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"></path></svg>
                  <?= escape($name) ?><?php if ($idx === 0): ?> <span style="color:var(--green);font-size:9px;">●</span><?php endif; ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
