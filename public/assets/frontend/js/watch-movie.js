/* ─── PARTICLES ─────────────────────────────────── */
function spawnParticles() {
    const container = document.getElementById('particles');
    const colors = ['#e8173f', '#00c8f0', '#8b5cf6', '#ffc340', '#ff5e7d'];
    for (let i = 0; i < 18; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 4 + 2;
        p.style.cssText = `
      width:${size}px;height:${size}px;
      left:${Math.random() * 100}%;
      background:${colors[Math.floor(Math.random() * colors.length)]};
      animation-duration:${Math.random() * 8 + 6}s;
      animation-delay:${Math.random() * 8}s;
    `;
        container.appendChild(p);
    }
}
spawnParticles();

/* ─── PLAYER STATE ───────────────────────────────── */
let isPlaying = false;
let isMuted = false;
let volume = 0.8;
let progress = 34; // percent
let isFullscreen = false;
let userRating = 0;

function initPlay() {
    isPlaying = true;
    updatePlayBtn();
    showToast('▶ Playing Neon Requiem (2024) · 4K HDR');
    simulateProgress();
}

function togglePlay() {
    isPlaying = !isPlaying;
    updatePlayBtn();
    showToast(isPlaying ? '▶ Playing' : '⏸ Paused');
    if (isPlaying) simulateProgress();
}

function updatePlayBtn() {
    const icon = document.getElementById('playIcon');
    if (isPlaying) {
        icon.setAttribute('viewBox', '0 0 24 24');
        icon.innerHTML = '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>';
    } else {
        icon.innerHTML = '<polygon points="5 3 19 12 5 21 5 3"/>';
    }
}

let progressInterval;
function simulateProgress() {
    clearInterval(progressInterval);
    if (!isPlaying) return;
    progressInterval = setInterval(() => {
        if (!isPlaying) { clearInterval(progressInterval); return; }
        progress = Math.min(progress + 0.1, 100);
        document.getElementById('progressFill').style.width = progress + '%';
        // Update time display
        const totalSecs = 138 * 60 + 4;
        const curSecs = Math.floor(totalSecs * progress / 100);
        const m = Math.floor(curSecs / 60);
        const s = curSecs % 60;
        document.getElementById('curTime').textContent = m + ':' + String(s).padStart(2, '0');
        if (progress >= 100) { clearInterval(progressInterval); isPlaying = false; updatePlayBtn(); }
    }, 300);
}

function seekVideo(e) {
    const bar = e.currentTarget;
    const rect = bar.getBoundingClientRect();
    progress = Math.max(0, Math.min(100, (e.clientX - rect.left) / rect.width * 100));
    document.getElementById('progressFill').style.width = progress + '%';
    showToast('Seeked to ' + Math.round(progress) + '%');
}

function skipBack() { progress = Math.max(0, progress - 5); document.getElementById('progressFill').style.width = progress + '%'; showToast('⏮ -10s'); }
function skipFwd() { progress = Math.min(100, progress + 5); document.getElementById('progressFill').style.width = progress + '%'; showToast('⏭ +10s'); }

function toggleMute() {
    isMuted = !isMuted;
    document.getElementById('volFill').style.width = isMuted ? '0%' : (volume * 100) + '%';
    showToast(isMuted ? '🔇 Muted' : '🔊 Unmuted');
}
function setVolume(e) {
    const track = e.currentTarget;
    const rect = track.getBoundingClientRect();
    volume = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
    document.getElementById('volFill').style.width = (volume * 100) + '%';
    isMuted = volume === 0;
}

function toggleFullscreen() {
    isFullscreen = !isFullscreen;
    showToast(isFullscreen ? '⛶ Fullscreen On' : '⛶ Fullscreen Off');
}

/* ─── SERVER / LANG ──────────────────────────────── */
function selectServer(el, name) {
    document.querySelectorAll('.server-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    showToast('Server: ' + name + ' selected');
}
function selectLang(el, type) {
    document.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    showToast(type === 'sub' ? '🔤 Subtitled (SUB) selected' : '🎙 Dubbed (DUB) selected');
}

/* ─── TABS ───────────────────────────────────────── */
function switchTab(id, btn) {
    document.querySelectorAll('.ctab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + id).classList.add('active');
}

/* ─── LIKE ───────────────────────────────────────── */
function toggleLike() {
    const btn = document.getElementById('likeBtn');
    btn.classList.toggle('liked');
    showToast(btn.classList.contains('liked') ? '❤️ Added to favorites' : '🤍 Removed from favorites');
}

/* ─── RATING ─────────────────────────────────────── */
function rateMovie(n) {
    userRating = n;
    document.querySelectorAll('.user-star').forEach((s, i) => {
        s.classList.toggle('active', i < n);
    });
    showToast('⭐ You rated this ' + n + '/5 — Thanks!');
}

/* ─── SEARCH ─────────────────────────────────────── */
document.getElementById('searchOpen').addEventListener('click', () => {
    document.getElementById('search-overlay').classList.add('open');
    setTimeout(() => document.getElementById('searchInput').focus(), 150);
});
document.getElementById('mobileSearchOpen').addEventListener('click', () => {
    document.getElementById('search-overlay').classList.add('open');
    setTimeout(() => document.getElementById('searchInput').focus(), 150);
});
document.getElementById('searchClose').addEventListener('click', () => {
    document.getElementById('search-overlay').classList.remove('open');
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('search-overlay').classList.remove('open');
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('search-overlay').classList.add('open');
        setTimeout(() => document.getElementById('searchInput').focus(), 150);
    }
});

/* ─── TOAST ──────────────────────────────────────── */
let toastTimer;
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 2200);
}