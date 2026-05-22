let userRating = 0;
let vexioVideo = null;
let streamLoaded = false;
let streamLoading = null;
let streamSources = [];
let streamSourceIndex = 0;
let streamStartTimer = null;
let vidstackModule = null;

function initVexioVideo() {
    if (vexioVideo) return vexioVideo;
    return document.querySelector('#vexioPlayerTarget media-player');
}

function sourceMimeType(source) {
    const type = String(source?.type || '').toLowerCase();
    const url = String(source?.url || '').toLowerCase();
    if (type === 'hls' || url.includes('.m3u8')) return 'application/vnd.apple.mpegurl';
    if (type === 'mkv' || url.includes('.mkv')) return 'video/x-matroska';
    if (type === 'webm' || url.includes('.webm')) return 'video/webm';
    if (type === 'ogg' || url.includes('.ogv') || url.includes('.ogg')) return 'video/ogg';
    return 'video/mp4';
}

function sourceCompatibilityMessage(source) {
    if (source?.compatibilityWarning) return source.compatibilityWarning;
    if (String(source?.type || '').toLowerCase() === 'mkv') {
        return 'This MKV source may not play in every browser.';
    }
    return '';
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

function isEnglishSubtitle(subtitle) {
    const value = String(`${subtitle?.label || ''} ${subtitle?.language || ''} ${subtitle?.lang || ''}`).toLowerCase();
    return /\b(en|eng|english)\b/.test(value);
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
        .sort((a, b) => Number(isEnglishSubtitle(b)) - Number(isEnglishSubtitle(a)))
        .slice(0, 14);
}

function createVidstackTracks(module, subtitles) {
    const tracks = normalizeSubtitles(subtitles);
    const defaultIndex = Math.max(0, tracks.findIndex(isEnglishSubtitle));
    return tracks.map((subtitle, index) => {
        const language = subtitleLanguage(subtitle);
        return new module.TextTrack({
            src: subtitle.url,
            kind: 'subtitles',
            label: subtitle.label,
            language,
            type: 'vtt',
            default: index === defaultIndex
        });
    });
}

function setPlayerLoading(isLoading, status = 'Connecting to vexio-main') {
    const wrap = document.getElementById('playerWrap');
    const statusNode = document.getElementById('vexioLoaderStatus');
    if (statusNode) statusNode.textContent = status;
    wrap?.classList.toggle('is-loading', isLoading);
}

async function loadVidstackModule() {
    if (!vidstackModule) {
        vidstackModule = await import('https://cdn.vidstack.io/player');
    }
    return vidstackModule;
}

async function createVidstackPlayer(source, subtitles) {
    const wrap = document.getElementById('playerWrap');
    const target = document.getElementById('vexioPlayerTarget');
    if (!wrap || !target || !source?.url) return null;

    const module = await loadVidstackModule();
    const { VidstackPlayer, VidstackPlayerLayout } = module;

    if (vexioVideo) {
        try { vexioVideo.pause?.(); } catch (_error) {}
    }
    target.replaceChildren();

    const mimeType = sourceMimeType(source);
    const tracks = createVidstackTracks(module, [...(subtitles || []), ...(source.subtitles || []), ...(source.tracks || [])]);
    const defaultTrack = tracks.find(track => track.default);
    if (defaultTrack) {
        try { defaultTrack.mode = 'showing'; } catch (_error) {}
    }
    const player = await VidstackPlayer.create({
        target,
        src: { src: source.url, type: mimeType },
        title: target.dataset.title || wrap.dataset.title || '',
        poster: target.dataset.poster || '',
        tracks,
        layout: new VidstackPlayerLayout({ colorScheme: 'dark' }),
        viewType: 'video',
        streamType: 'on-demand',
        preload: 'metadata',
        playsinline: true,
        crossOrigin: 'anonymous',
        logLevel: 'warn'
    });

    vexioVideo = player;
    return player;
}

async function playSource(source, subtitles, shouldPlay = false) {
    const wrap = document.getElementById('playerWrap');
    if (!wrap || !source?.url) return false;

    const compatibilityMessage = sourceCompatibilityMessage(source);
    streamLoaded = false;
    wrap.classList.remove('is-ready');
    setPlayerLoading(true, 'Preparing vexio-main');
    clearTimeout(streamStartTimer);
    const player = await createVidstackPlayer(source, subtitles);
    if (!player) return false;

    const startPlayback = () => {
        if (streamLoaded) return;
        clearTimeout(streamStartTimer);
        streamLoaded = true;
        wrap.classList.add('is-ready');
        setPlayerLoading(false);
        if (compatibilityMessage) showToast(compatibilityMessage);
        if (shouldPlay) Promise.resolve(player.play()).catch(() => {});
    };
    const tryNextSource = async () => {
        clearTimeout(streamStartTimer);
        if (streamLoaded) return;
        const nextSource = streamSources[streamSourceIndex + 1];
        if (!nextSource) {
            setPlayerLoading(false);
            showToast(compatibilityMessage || 'This stream is taking too long to start');
            return;
        }
        streamSourceIndex += 1;
        setPlayerLoading(true, 'Trying vexio-main fallback');
        showToast('Trying another stream');
        await playSource(nextSource, subtitles, shouldPlay);
    };

    player.addEventListener('can-play', startPlayback, { once: true });
    player.addEventListener('loaded-metadata', startPlayback, { once: true });
    player.addEventListener('error', tryNextSource, { once: true });
    streamStartTimer = setTimeout(tryNextSource, 15000);

    return true;
}

async function loadVexioStream(shouldPlay = false) {
    if (streamLoaded) {
        if (shouldPlay) Promise.resolve(vexioVideo?.play?.()).catch(() => {});
        return true;
    }

    if (streamLoading) {
        const loaded = await streamLoading;
        if (loaded && shouldPlay) Promise.resolve(vexioVideo?.play?.()).catch(() => {});
        return loaded;
    }

    const endpoint = document.getElementById('playerWrap')?.dataset.playerSourceUrl || '';
    if (!endpoint) return false;

    streamLoading = (async () => {
        try {
            setPlayerLoading(true, 'Connecting to vexio-main');
            showToast('Loading stream');
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (!response.ok || data.error) throw new Error(data.error?.message || 'Stream request failed');

            streamSources = (Array.isArray(data.sources) ? data.sources : []).filter(item => item?.url);
            streamSourceIndex = 0;
            const source = streamSources[streamSourceIndex];
            if (!source) throw new Error('No playable stream found');

            return await playSource(source, data.subtitles || [], shouldPlay);
        } catch (error) {
            setPlayerLoading(false);
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
function skipBack() { if (vexioVideo) vexioVideo.currentTime = Math.max(0, vexioVideo.currentTime - 10); }
function skipFwd() { if (vexioVideo) vexioVideo.currentTime = Math.min(vexioVideo.duration || Infinity, vexioVideo.currentTime + 10); }
function toggleMute() { if (vexioVideo) vexioVideo.muted = !vexioVideo.muted; }
function setVolume() {}
function toggleFullscreen() {
    const wrap = document.getElementById('playerWrap');
    if (document.fullscreenElement) {
        document.exitFullscreen?.();
    } else {
        wrap?.requestFullscreen?.();
    }
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
        initVexioVideo();
        loadVexioStream(false);
    });
} else {
    initVexioVideo();
    loadVexioStream(false);
}
