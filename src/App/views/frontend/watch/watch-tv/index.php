<?= $this->start('styles') ?>
<link rel="stylesheet" href="/assets/frontend/css/watch-tv-show.css">
<?= $this->end() ?>

<?= $this->start('content') ?>
<div class="page-wrap">
  <?= $this->includePartial('/frontend/watch/watch-tv/ad/tv-top-ad') ?>

  <div class="watch-layout">
    <div class="watch-main">
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-player') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-server-selector') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/quick-episode') ?>
      <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-content') ?>
    </div>

    <?= $this->includePartial('/frontend/watch/watch-tv/components/tv-sidebar') ?>
  </div>
</div>
<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script src="/assets/frontend/js/watch-tv.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-tv.js') ?>"></script>
<script src="/assets/frontend/js/watch-comments.js?v=<?= filemtime(dirname(__DIR__, 6) . '/public/assets/frontend/js/watch-comments.js') ?>"></script>
<script>
const EPISODE_TOTAL_SECONDS = 45 * 60;
let tvIsPlaying = false;
let tvIsMuted = false;
let tvVolume = 0.8;
let tvProgress = 0;
let tvProgressTimer;
let tvCountdownTimer;

function mountEmbeddedPlayer() {
  const wrap = document.getElementById('playerWrap');
  const frame = document.getElementById('embeddedPlayerFrame');
  const embedUrl = wrap?.dataset.playerEmbedUrl || '';

  if (!wrap || !frame || !embedUrl) return false;

  if (!frame.src) {
    frame.src = embedUrl;
  }

  wrap.classList.add('has-embed');
  return true;
}

function loadEmbedUrl(embedUrl) {
  const wrap = document.getElementById('playerWrap');
  const frame = document.getElementById('embeddedPlayerFrame');

  if (!wrap || !frame || !embedUrl) return false;

  wrap.dataset.playerEmbedUrl = embedUrl;
  if (wrap.classList.contains('has-embed') && frame.src !== embedUrl) {
    frame.src = embedUrl;
  }
  return true;
}

function spawnTvParticles() {
  const container = document.getElementById('particles');
  if (!container || container.children.length) return;
  const colors = ['#00c8f0', '#8b5cf6', '#e8173f', '#ffc340', '#ff5e7d'];
  for (let i = 0; i < 18; i++) {
    const p = document.createElement('div');
    const size = Math.random() * 4 + 2;
    p.className = 'particle';
    p.style.cssText = `width:${size}px;height:${size}px;left:${Math.random() * 100}%;background:${colors[Math.floor(Math.random() * colors.length)]};animation-duration:${Math.random() * 8 + 6}s;animation-delay:${Math.random() * 8}s;`;
    container.appendChild(p);
  }
}

function updatePlayBtn() {
  const icon = document.getElementById('playIcon');
  if (!icon) return;
  icon.innerHTML = tvIsPlaying
    ? '<rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect>'
    : '<polygon points="5 3 19 12 5 21 5 3"></polygon>';
}

function initPlay() {
  if (mountEmbeddedPlayer()) {
    tvIsPlaying = true;
    updatePlayBtn();
    showToast('Loading stream');
    return;
  }

  tvIsPlaying = true;
  updatePlayBtn();
  showToast('Playing episode');
  simulateProgress();
}

function togglePlay() {
  if (mountEmbeddedPlayer()) {
    tvIsPlaying = true;
    updatePlayBtn();
    showToast('Use the embedded player controls');
    return;
  }

  tvIsPlaying = !tvIsPlaying;
  updatePlayBtn();
  showToast(tvIsPlaying ? 'Playing' : 'Paused');
  if (tvIsPlaying) simulateProgress();
}

function simulateProgress() {
  clearInterval(tvProgressTimer);
  tvProgressTimer = setInterval(() => {
    if (!tvIsPlaying) {
      clearInterval(tvProgressTimer);
      return;
    }
    tvProgress = Math.min(tvProgress + 0.05, 100);
    const fill = document.getElementById('progressFill');
    const time = document.getElementById('curTime');
    if (fill) fill.style.width = tvProgress + '%';
    if (time) {
      const curSecs = Math.floor(EPISODE_TOTAL_SECONDS * tvProgress / 100);
      time.textContent = Math.floor(curSecs / 60) + ':' + String(curSecs % 60).padStart(2, '0');
    }
    if (tvProgress >= 98) triggerNextEp();
  }, 300);
}

function seekVideo(e) {
  const rect = e.currentTarget.getBoundingClientRect();
  tvProgress = Math.max(0, Math.min(100, (e.clientX - rect.left) / rect.width * 100));
  document.getElementById('progressFill').style.width = tvProgress + '%';
  showToast('Seeked to ' + Math.round(tvProgress) + '%');
}

function skipBack() { tvProgress = Math.max(0, tvProgress - 4); document.getElementById('progressFill').style.width = tvProgress + '%'; showToast('-10s'); }
function skipFwd() { tvProgress = Math.min(100, tvProgress + 4); document.getElementById('progressFill').style.width = tvProgress + '%'; showToast('+10s'); }
function toggleMute() { tvIsMuted = !tvIsMuted; document.getElementById('volFill').style.width = tvIsMuted ? '0%' : (tvVolume * 100) + '%'; showToast(tvIsMuted ? 'Muted' : 'Unmuted'); }
function setVolume(e) { const rect = e.currentTarget.getBoundingClientRect(); tvVolume = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)); document.getElementById('volFill').style.width = (tvVolume * 100) + '%'; tvIsMuted = tvVolume === 0; }
function toggleFullscreen() { showToast('Fullscreen toggled'); }

function triggerNextEp() {
  const overlay = document.getElementById('nextEpOverlay');
  if (!overlay || overlay.dataset.hasNext !== '1') {
    showToast('No next episode available');
    return;
  }
  overlay.classList.add('show');
  let count = 5;
  document.getElementById('nextCountdown').textContent = count;
  clearInterval(tvCountdownTimer);
  tvCountdownTimer = setInterval(() => {
    count--;
    document.getElementById('nextCountdown').textContent = count;
    if (count <= 0) playNextEpisode();
  }, 1000);
}

function playNextEpisode() {
  clearInterval(tvCountdownTimer);
  const overlay = document.getElementById('nextEpOverlay');
  const url = overlay ? overlay.dataset.nextUrl : '';
  if (url) window.location.href = url;
}

function cancelNextEp() {
  clearInterval(tvCountdownTimer);
  document.getElementById('nextEpOverlay')?.classList.remove('show');
  showToast('Autoplay cancelled');
}

function selectServer(el, name) { name = name || el.dataset.serverName || 'Server'; document.querySelectorAll('.server-tab').forEach(t => t.classList.remove('active')); el.classList.add('active'); loadEmbedUrl(el.dataset.embedUrl || ''); showToast('Server: ' + name + ' selected'); }
function switchTab(id, btn) { document.querySelectorAll('.ctab').forEach(t => t.classList.remove('active')); document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active')); btn.classList.add('active'); document.getElementById('tab-' + id)?.classList.add('active'); }
function toggleLike() { const btn = document.getElementById('likeBtn'); btn?.classList.toggle('liked'); showToast(btn?.classList.contains('liked') ? 'Added to favorites' : 'Removed from favorites'); }
function rateEp(n) { document.querySelectorAll('.user-star').forEach((s, i) => s.classList.toggle('active', i < n)); showToast('You rated this episode ' + n + '/5'); }

spawnTvParticles();
</script>
<?= $this->end() ?>
