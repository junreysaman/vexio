let userRating = 0;
let vexioPlyr = null;
let vexioHls = null;
let streamLoaded = false;
let streamLoading = null;

function initVexioPlyr(options = {}) {
    const video = document.getElementById('vexioPlyrVideo');
    if (!video || !window.Plyr) return null;
    if (vexioPlyr) return vexioPlyr;

    vexioPlyr = new Plyr(video, {
        iconUrl: '/assets/vendor/plyr/plyr.svg',
        controls: ['play-large', 'play', 'rewind', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'],
        settings: ['captions', 'quality', 'speed'],
        captions: { active: true, language: 'auto', update: true },
        seekTime: 10,
        ...options
    });

    video.addEventListener('play', () => {
        if (!streamLoaded) {
            video.pause();
            loadVexioStream(true);
        }
    });

    return vexioPlyr;
}

function sourceMimeType(source) {
    const type = String(source?.type || '').toLowerCase();
    const url = String(source?.url || '').toLowerCase();
    if (type === 'hls' || url.includes('.m3u8')) return 'application/vnd.apple.mpegurl';
    if (type === 'webm' || url.includes('.webm')) return 'video/webm';
    if (type === 'ogg' || url.includes('.ogv') || url.includes('.ogg')) return 'video/ogg';
    return 'video/mp4';
}

function subtitleLanguage(subtitle) {
    const raw = String(subtitle.language || subtitle.lang || subtitle.srclang || subtitle.label || '').trim();
    const label = raw.toLowerCase();
    const languages = {
        english: 'en', eng: 'en',
        spanish: 'es', spa: 'es',
        french: 'fr', fre: 'fr', fra: 'fr',
        german: 'de', ger: 'de', deu: 'de',
        portuguese: 'pt', por: 'pt',
        hindi: 'hi', hin: 'hi',
        indonesian: 'id', ind: 'id',
        malay: 'ms', may: 'ms', msa: 'ms',
        japanese: 'ja', jpn: 'ja',
        korean: 'ko', kor: 'ko',
        chinese: 'zh', chi: 'zh', zho: 'zh'
    };

    return languages[label] || label.replace(/[^a-z-]/g, '').slice(0, 12) || 'sub';
}

function isVttSubtitle(subtitle) {
    const format = String(subtitle.format || subtitle.type || '').toLowerCase();
    const url = String(subtitle.url || subtitle.src || subtitle.file || '').toLowerCase();
    return !format || ['vtt', 'webvtt'].includes(format) || url.includes('/sub-proxy') || url.includes('.vtt');
}

function normalizeSubtitles(subtitles) {
    const seen = new Set();
    return (Array.isArray(subtitles) ? subtitles : [])
        .map(subtitle => ({
            ...subtitle,
            url: subtitle?.url || subtitle?.src || subtitle?.file || '',
            label: subtitle?.label || subtitle?.language || subtitle?.lang || 'Subtitle'
        }))
        .filter(subtitle => subtitle.url && isVttSubtitle(subtitle))
        .filter(subtitle => {
            if (seen.has(subtitle.url)) return false;
            seen.add(subtitle.url);
            return true;
        })
        .slice(0, 14);
}

function addSubtitles(video, subtitles) {
    const tracks = normalizeSubtitles(subtitles);
    video.querySelectorAll('track').forEach(track => track.remove());

    tracks.forEach((subtitle, index) => {
        const track = document.createElement('track');
        const language = subtitleLanguage(subtitle);
        track.kind = 'subtitles';
        track.src = subtitle.url;
        track.label = subtitle.label;
        track.srclang = language;
        track.default = index === 0 && /^(en|english|eng)$/i.test(String(subtitle.label || subtitle.language || language));
        video.appendChild(track);
    });

    return tracks;
}

function applyHlsQualityLevels(player, hls) {
    hls.on(Hls.Events.MANIFEST_PARSED, () => {
        const heights = hls.levels
            .map(level => level.height)
            .filter((height, index, levels) => height && levels.indexOf(height) === index)
            .sort((a, b) => b - a);

        if (!heights.length) return;

        player.config.quality = {
            default: 0,
            options: [0, ...heights],
            forced: true,
            onChange: quality => {
                hls.currentLevel = quality === 0 ? -1 : hls.levels.findIndex(level => level.height === quality);
            }
        };
    });
}

function playSource(source, subtitles, shouldPlay = false) {
    const wrap = document.getElementById('playerWrap');
    const video = document.getElementById('vexioPlyrVideo');
    const player = initVexioPlyr();
    if (!wrap || !video || !player || !source?.url) return false;

    addSubtitles(video, [...(subtitles || []), ...(source.subtitles || []), ...(source.tracks || [])]);

    if (vexioHls) {
        vexioHls.destroy();
        vexioHls = null;
    }

    const mimeType = sourceMimeType(source);
    const startPlayback = () => {
        streamLoaded = true;
        wrap.classList.add('is-ready');
        if (shouldPlay) Promise.resolve(player.play()).catch(() => {});
    };

    if (mimeType.includes('mpegurl') && window.Hls && Hls.isSupported()) {
        vexioHls = new Hls({ enableWorker: true });
        applyHlsQualityLevels(player, vexioHls);
        vexioHls.loadSource(source.url);
        vexioHls.attachMedia(video);
        vexioHls.on(Hls.Events.MANIFEST_PARSED, startPlayback);
        vexioHls.on(Hls.Events.ERROR, (_event, data) => {
            if (data?.fatal) showToast('Stream playback failed');
        });
    } else {
        video.src = source.url;
        video.type = mimeType;
        video.addEventListener('loadedmetadata', startPlayback, { once: true });
        video.load();
    }

    return true;
}

async function loadVexioStream(shouldPlay = false) {
    if (streamLoaded) {
        if (shouldPlay) Promise.resolve(vexioPlyr?.play?.()).catch(() => {});
        return true;
    }

    if (streamLoading) {
        const loaded = await streamLoading;
        if (loaded && shouldPlay) Promise.resolve(vexioPlyr?.play?.()).catch(() => {});
        return loaded;
    }

    const endpoint = document.getElementById('playerWrap')?.dataset.playerSourceUrl || '';
    if (!endpoint) return false;

    streamLoading = (async () => {
        try {
            showToast('Loading stream');
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (!response.ok || data.error) throw new Error(data.error?.message || 'Stream request failed');

            const source = (Array.isArray(data.sources) ? data.sources : []).find(item => item?.url);
            if (!source) throw new Error('No playable stream found');

            return playSource(source, data.subtitles || [], shouldPlay);
        } catch (error) {
            showToast(error.message || 'Unable to load stream');
            return false;
        } finally {
            streamLoading = null;
        }
    })();

    return streamLoading;
}

function initPlay() { loadVexioStream(true); }
function togglePlay() { loadVexioStream(true); }
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initVexioPlyr();
        loadVexioStream(false);
    });
} else {
    initVexioPlyr();
    loadVexioStream(false);
}
