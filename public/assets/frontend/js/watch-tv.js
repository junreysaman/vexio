function initEpisodeList() {
    const panel = document.querySelector('[data-episode-list]');
    if (!panel) return;

    const search = panel.querySelector('[data-episode-search]');
    const list = panel.querySelector('.ep-list');
    const rows = Array.from(panel.querySelectorAll('.ep-row'));
    const loadMore = panel.querySelector('[data-episode-load-more]');
    const loadMoreWrap = loadMore?.closest('.ep-load-more-wrap');
    const count = panel.querySelector('[data-episode-visible-count]');
    const pageSize = parseInt(panel.dataset.pageSize || '10', 10);
    let visibleLimit = pageSize;

    const render = () => {
        const term = (search?.value || '').trim().toLowerCase();
        const isNumericTerm = /^(episode|ep|e)?\s*\d+$/i.test(term) || /^s\d+\s*e\d+$/i.test(term);
        const normalizedNumberTerm = term.match(/\d+/)?.[0] || '';
        const matches = rows.filter(row => {
            if (!term) return true;
            if (isNumericTerm && normalizedNumberTerm) {
                return (` ${row.dataset.numberSearch || ''} `).includes(` ${normalizedNumberTerm} `)
                    || (` ${row.dataset.numberSearch || ''} `).includes(` 0${normalizedNumberTerm} `)
                    || (` ${row.dataset.numberSearch || ''} `).includes(` episode ${normalizedNumberTerm} `)
                    || (` ${row.dataset.numberSearch || ''} `).includes(` e${normalizedNumberTerm} `);
            }
            return (row.dataset.search || '').includes(term)
                || (row.dataset.numberSearch || '').includes(term);
        });

        rows.forEach(row => {
            row.hidden = true;
        });
        matches.slice(0, visibleLimit).forEach(row => {
            row.hidden = false;
        });

        if (loadMore) loadMore.hidden = matches.length <= visibleLimit;
        if (loadMoreWrap) loadMoreWrap.hidden = matches.length <= visibleLimit;
        if (count) count.textContent = `${Math.min(matches.length, visibleLimit).toLocaleString()} of ${matches.length.toLocaleString()} episodes`;

        list?.classList.toggle('is-empty', matches.length === 0);
        panel.querySelector('.ep-empty')?.toggleAttribute('hidden', matches.length !== 0);
    };

    search?.addEventListener('input', () => {
        visibleLimit = pageSize;
        render();
    });
    loadMore?.addEventListener('click', () => {
        visibleLimit += pageSize;
        render();
    });
    render();
}

function initSidebarEpisodeList() {
    const scroller = document.querySelector('[data-sidebar-episodes]');
    if (!scroller) return;

    const rows = Array.from(scroller.querySelectorAll('[data-sidebar-episode]'));
    const sentinel = scroller.querySelector('[data-sidebar-episode-sentinel]');
    const pageSize = Math.max(1, parseInt(scroller.dataset.pageSize || '12', 10));
    const currentIndex = Math.max(0, rows.findIndex(row => row.dataset.currentEpisode === '1'));
    let visibleLimit = Math.max(pageSize, Math.ceil((currentIndex + 1) / pageSize) * pageSize);

    const render = () => {
        rows.forEach((row, index) => {
            row.hidden = index >= visibleLimit;
        });
        if (sentinel) sentinel.hidden = visibleLimit >= rows.length;
    };

    const revealMore = () => {
        if (visibleLimit >= rows.length) return;
        visibleLimit = Math.min(rows.length, visibleLimit + pageSize);
        render();
    };

    if ('IntersectionObserver' in window && sentinel) {
        const observer = new IntersectionObserver((entries) => {
            if (entries.some(entry => entry.isIntersecting)) revealMore();
        }, { root: scroller, rootMargin: '160px 0px' });
        observer.observe(sentinel);
    } else {
        scroller.addEventListener('scroll', () => {
            if (scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 160) revealMore();
        }, { passive: true });
    }

    render();
    rows[currentIndex]?.scrollIntoView({ block: 'nearest' });
}

let vexioPlyr = null;
let vexioHls = null;
let streamLoaded = false;
let tvCountdownTimer;

function initVexioPlyr() {
    const video = document.getElementById('vexioPlyrVideo');
    if (!video || !window.Plyr) return null;
    if (vexioPlyr) return vexioPlyr;

    vexioPlyr = new Plyr(video, {
        iconUrl: '/assets/vendor/plyr/plyr.svg',
        controls: ['play-large', 'play', 'rewind', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
        settings: ['captions', 'quality', 'speed'],
        seekTime: 10
    });
    video.addEventListener('ended', triggerNextEp);
    return vexioPlyr;
}

function sourceMimeType(source) {
    const type = String(source?.type || '').toLowerCase();
    const url = String(source?.url || '').toLowerCase();
    return type === 'mp4' || url.includes('.mp4') ? 'video/mp4' : 'application/vnd.apple.mpegurl';
}

function subtitleLanguage(subtitle) {
    const label = String(subtitle.label || subtitle.language || '').toLowerCase();
    if (label.includes('english') || label === 'eng') return 'en';
    if (label.includes('spanish') || label === 'spa') return 'es';
    if (label.includes('french') || label === 'fre') return 'fr';
    if (label.includes('german') || label === 'ger') return 'de';
    return label.replace(/[^a-z-]/g, '').slice(0, 12) || 'sub';
}

function addSubtitles(video, subtitles) {
    video.querySelectorAll('track').forEach(track => track.remove());
    (Array.isArray(subtitles) ? subtitles : [])
        .filter(subtitle => subtitle?.url && String(subtitle.format || 'vtt').toLowerCase() === 'vtt')
        .slice(0, 14)
        .forEach((subtitle, index) => {
            const track = document.createElement('track');
            track.kind = 'subtitles';
            track.src = subtitle.url;
            track.label = subtitle.label || 'Subtitle';
            track.srclang = subtitleLanguage(subtitle);
            track.default = index === 0 && /english|eng|^en$/i.test(String(subtitle.label || subtitle.language || ''));
            video.appendChild(track);
        });
}

function playSource(source, subtitles) {
    const wrap = document.getElementById('playerWrap');
    const video = document.getElementById('vexioPlyrVideo');
    const player = initVexioPlyr();
    if (!wrap || !video || !player || !source?.url) return false;

    addSubtitles(video, subtitles);

    if (vexioHls) {
        vexioHls.destroy();
        vexioHls = null;
    }

    const mimeType = sourceMimeType(source);
    if (mimeType.includes('mpegurl') && window.Hls && Hls.isSupported()) {
        vexioHls = new Hls({ enableWorker: true });
        vexioHls.loadSource(source.url);
        vexioHls.attachMedia(video);
        vexioHls.on(Hls.Events.MANIFEST_PARSED, () => {
            wrap.classList.add('is-ready');
            Promise.resolve(player.play()).catch(() => {});
        });
        vexioHls.on(Hls.Events.ERROR, (_event, data) => {
            if (data?.fatal) showToast('Stream playback failed');
        });
    } else {
        video.src = source.url;
        video.type = mimeType;
        wrap.classList.add('is-ready');
        video.addEventListener('loadedmetadata', () => Promise.resolve(player.play()).catch(() => {}), { once: true });
    }

    streamLoaded = true;
    showToast('Loading stream');
    return true;
}

async function loadVexioStream() {
    if (streamLoaded) {
        Promise.resolve(vexioPlyr?.play?.()).catch(() => {});
        return true;
    }

    const endpoint = document.getElementById('playerWrap')?.dataset.playerSourceUrl || '';
    if (!endpoint) return false;

    try {
        showToast('Loading stream');
        const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!response.ok || data.error) throw new Error(data.error?.message || 'Stream request failed');

        const source = (Array.isArray(data.sources) ? data.sources : []).find(item => item?.url);
        if (!source) throw new Error('No playable stream found');

        return playSource(source, data.subtitles || []);
    } catch (error) {
        showToast(error.message || 'Unable to load stream');
        return false;
    }
}

function initPlay() { loadVexioStream(); }
function togglePlay() {
    if (!streamLoaded) {
        loadVexioStream();
        return;
    }
    vexioPlyr?.togglePlay?.();
}
function seekVideo() {}
function skipBack() { vexioPlyr?.rewind?.(10); }
function skipFwd() { vexioPlyr?.forward?.(10); }
function toggleMute() { if (vexioPlyr) vexioPlyr.muted = !vexioPlyr.muted; }
function setVolume() {}
function toggleFullscreen() { vexioPlyr?.fullscreen?.toggle?.(); }

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

function triggerNextEp() {
    const overlay = document.getElementById('nextEpOverlay');
    if (!overlay || overlay.dataset.hasNext !== '1') return;
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
    const url = document.getElementById('nextEpOverlay')?.dataset.nextUrl || '';
    if (url) window.location.href = url;
}

function cancelNextEp() {
    clearInterval(tvCountdownTimer);
    document.getElementById('nextEpOverlay')?.classList.remove('show');
    showToast('Autoplay cancelled');
}

function switchTab(id, btn) {
    document.querySelectorAll('.ctab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + id)?.classList.add('active');
}

function toggleLike() {
    const btn = document.getElementById('likeBtn');
    btn?.classList.toggle('liked');
    showToast(btn?.classList.contains('liked') ? 'Added to favorites' : 'Removed from favorites');
}

function rateEp(n) {
    document.querySelectorAll('.user-star').forEach((s, i) => s.classList.toggle('active', i < n));
    showToast('You rated this episode ' + n + '/5');
}

function showToast(msg) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(window.watchTvToastTimer);
    window.watchTvToastTimer = setTimeout(() => t.classList.remove('show'), 2200);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initEpisodeList();
        initSidebarEpisodeList();
        spawnTvParticles();
        initVexioPlyr();
    });
} else {
    initEpisodeList();
    initSidebarEpisodeList();
    spawnTvParticles();
    initVexioPlyr();
}
