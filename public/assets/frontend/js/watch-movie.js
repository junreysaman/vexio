function spawnParticles() {
    const container = document.getElementById('particles');
    if (!container || container.children.length) return;
    const colors = ['#e8173f', '#00c8f0', '#8b5cf6', '#ffc340', '#ff5e7d'];
    for (let i = 0; i < 18; i++) {
        const p = document.createElement('div');
        const size = Math.random() * 4 + 2;
        p.className = 'particle';
        p.style.cssText = `width:${size}px;height:${size}px;left:${Math.random() * 100}%;background:${colors[Math.floor(Math.random() * colors.length)]};animation-duration:${Math.random() * 8 + 6}s;animation-delay:${Math.random() * 8}s;`;
        container.appendChild(p);
    }
}

let userRating = 0;
let vexioPlyr = null;
let vexioHls = null;
let streamLoaded = false;

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

function rateMovie(n) {
    userRating = n;
    document.querySelectorAll('.user-star').forEach((s, i) => {
        s.classList.toggle('active', i < n);
    });
    showToast('You rated this ' + n + '/5');
}

document.getElementById('searchOpen')?.addEventListener('click', () => {
    document.getElementById('search-overlay')?.classList.add('open');
    setTimeout(() => document.getElementById('searchInput')?.focus(), 150);
});
document.getElementById('mobileSearchOpen')?.addEventListener('click', () => {
    document.getElementById('search-overlay')?.classList.add('open');
    setTimeout(() => document.getElementById('searchInput')?.focus(), 150);
});
document.getElementById('searchClose')?.addEventListener('click', () => {
    document.getElementById('search-overlay')?.classList.remove('open');
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('search-overlay')?.classList.remove('open');
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('search-overlay')?.classList.add('open');
        setTimeout(() => document.getElementById('searchInput')?.focus(), 150);
    }
});

function showToast(msg) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(window.watchMovieToastTimer);
    window.watchMovieToastTimer = setTimeout(() => t.classList.remove('show'), 2200);
}

spawnParticles();
initVexioPlyr();
