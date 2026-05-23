let userRating = 0;
let vexioVideo = null;
let streamLoaded = false;
let streamLoading = null;
let streamSources = [];
let streamSourceIndex = 0;
let streamStartTimer = null;
let streamAttemptId = 0;
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

function setPlayerUnavailable(message = 'No playable source found') {
    const wrap = document.getElementById('playerWrap');
    const unavailable = document.getElementById('vexioPlayerUnavailable');
    const detail = document.getElementById('vexioUnavailableDetail');
    const statusNode = document.getElementById('vexioLoaderStatus');
    clearTimeout(streamStartTimer);
    streamLoaded = false;
    wrap?.classList.remove('is-ready', 'is-loading', 'is-player-rendering');
    wrap?.classList.add('is-unavailable');
    if (statusNode) statusNode.textContent = message;
    if (detail) detail.textContent = message;
    if (unavailable) unavailable.hidden = false;
}

function clearPlayerUnavailable() {
    const wrap = document.getElementById('playerWrap');
    const unavailable = document.getElementById('vexioPlayerUnavailable');
    wrap?.classList.remove('is-unavailable');
    if (unavailable) unavailable.hidden = true;
}

function clearEmbedPlayer() {
    const wrap = document.getElementById('playerWrap');
    wrap?.classList.remove('is-embed', 'is-player-rendering');
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
        try { vexioVideo.pause?.(); } catch (_error) { }
    }
    target.replaceChildren();

    const mimeType = sourceMimeType(source);
    const tracks = createVidstackTracks(module, [...(subtitles || []), ...(source.subtitles || []), ...(source.tracks || [])]);
    const defaultTrack = tracks.find(track => track.default);
    if (defaultTrack) {
        try { defaultTrack.mode = 'showing'; } catch (_error) { }
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

    const attemptId = ++streamAttemptId;
    const compatibilityMessage = sourceCompatibilityMessage(source);
    streamLoaded = false;
    wrap.classList.remove('is-ready');
    clearPlayerUnavailable();
    clearEmbedPlayer();
    setPlayerLoading(true, compatibilityMessage || 'Preparing vexio-main');
    clearTimeout(streamStartTimer);
    let player = null;
    try {
        player = await createVidstackPlayer(source, subtitles);
    } catch (_error) {
        player = null;
    }
    if (!player) {
        setPlayerUnavailable('Unable to prepare this stream');
        return false;
    }

    const startPlayback = () => {
        if (streamLoaded || attemptId !== streamAttemptId) return;
        clearTimeout(streamStartTimer);
        streamLoaded = true;
        // show an intermediate rendering state and wait for actual media frame
        wrap.classList.add('is-player-rendering');

        const target = document.getElementById('vexioPlayerTarget');

        // Keep the external loader visible until we actually get a rendered frame.
        // This prevents flicker in production where Vidstack may satisfy readyState/loadeddata early.
        const finalizeReady = () => {
            if (attemptId !== streamAttemptId) return;
            wrap.classList.remove('is-player-rendering');
            wrap.classList.add('is-ready');
            setPlayerLoading(false);
            if (shouldPlay) Promise.resolve(player.play()).catch(() => { });
        };

        const videoEl = target?.querySelector('video');

        const markReadyAfterFrame = (v) => {
            if (!v) return;
            if (attemptId !== streamAttemptId) return;
            // Some browsers keep readyState >= 3 but never present a frame.
            // We want at least one frame callback or a strong loadeddata+dimension signal.

            // Prefer requestVideoFrameCallback when available.
            if (typeof v.requestVideoFrameCallback === 'function') {
                try {
                    v.requestVideoFrameCallback(() => finalizeReady());
                    // Safety timeout: if callback never fires, fall back shortly.
                    setTimeout(finalizeReady, 2500);
                    return;
                } catch (_e) {
                    // continue to fallback
                }
            }

            const isProbablyRenderable = () => {
                try {
                    return (v.videoWidth > 0 && v.videoHeight > 0 && v.readyState >= 2);
                } catch (_e) {
                    return false;
                }
            };

            const onTryFinalize = () => {
                v.removeEventListener('playing', onTryFinalize);
                v.removeEventListener('loadeddata', onTryFinalize);
                if (attemptId !== streamAttemptId) return;
                if (isProbablyRenderable()) return finalizeReady();
            };

            v.addEventListener('playing', onTryFinalize, { once: true });
            v.addEventListener('loadeddata', onTryFinalize, { once: true });

            // Fallback polling (kept short)
            let checks = 0;
            const poll = () => {
                if (attemptId !== streamAttemptId) return;
                if (isProbablyRenderable()) return finalizeReady();
                checks += 1;
                if (checks < 12) return requestAnimationFrame(poll);
                setTimeout(finalizeReady, 400);
            };
            requestAnimationFrame(poll);
        };

        if (videoEl) {
            markReadyAfterFrame(videoEl);
        } else {
            // Wait until vidstack injects the actual <video> element.
            let checks = 0;
            const checkLoop = () => {
                if (attemptId !== streamAttemptId) return;
                const v = target?.querySelector('video');
                if (v) return markReadyAfterFrame(v);
                checks += 1;
                if (checks < 30) return requestAnimationFrame(checkLoop);
                setTimeout(finalizeReady, 600);
            };
            requestAnimationFrame(checkLoop);
        }
    };
    const tryNextSource = async () => {
        clearTimeout(streamStartTimer);
        if (streamLoaded || attemptId !== streamAttemptId) return;
        const nextSource = streamSources[streamSourceIndex + 1];
        if (!nextSource) {
            setPlayerUnavailable('No playable source found for this movie');
            return;
        }
        streamSourceIndex += 1;
        setPlayerLoading(true, 'Trying vexio-main fallback');
        await playSource(nextSource, subtitles, shouldPlay);
    };

    player.addEventListener('can-play', startPlayback, { once: true });
    player.addEventListener('error', tryNextSource, { once: true });
    streamStartTimer = setTimeout(tryNextSource, 15000);

    return true;
}

async function loadVexioStream(shouldPlay = false) {
    if (streamLoaded) {
        if (shouldPlay) Promise.resolve(vexioVideo?.play?.()).catch(() => { });
        return true;
    }

    if (streamLoading) {
        const loaded = await streamLoading;
        if (loaded && shouldPlay) Promise.resolve(vexioVideo?.play?.()).catch(() => { });
        return loaded;
    }

    const endpoint = document.getElementById('playerWrap')?.dataset.playerSourceUrl || '';
    if (!endpoint) return false;

    streamLoading = (async () => {
        try {
            setPlayerLoading(true, 'Scanning servers');
            clearPlayerUnavailable();
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (!response.ok || data.error) throw new Error(data.error?.message || 'Stream request failed');

            streamSources = (Array.isArray(data.sources) ? data.sources : [])
                .filter(item => item?.url && item.browserPlayable !== false);
            streamSourceIndex = 0;
            const source = streamSources[streamSourceIndex];
            if (!source) {
                setPlayerUnavailable('No playable source found for this movie');
                return false;
            }

            return await playSource(source, data.subtitles || [], shouldPlay);
        } catch (error) {
            setPlayerUnavailable(error.message || 'Unable to load stream');
            return false;
        } finally {
            streamLoading = null;
        }
    })();

    return streamLoading;
}

function initPlay() { loadVexioStream(true); }
function togglePlay() { loadVexioStream(true); }
function seekVideo() { }
function skipBack() { if (vexioVideo) vexioVideo.currentTime = Math.max(0, vexioVideo.currentTime - 10); }
function skipFwd() { if (vexioVideo) vexioVideo.currentTime = Math.min(vexioVideo.duration || Infinity, vexioVideo.currentTime + 10); }
function toggleMute() { if (vexioVideo) vexioVideo.muted = !vexioVideo.muted; }
function setVolume() { }
function toggleFullscreen() {
    const wrap = document.getElementById('playerWrap');
    if (document.fullscreenElement) {
        document.exitFullscreen?.();
    } else {
        wrap?.requestFullscreen?.();
    }
}

function selectServer(button, _serverKey) {
    const wrap = document.getElementById('playerWrap');
    const target = document.getElementById('vexioPlayerTarget');
    document.querySelectorAll('.server-tab').forEach(tab => tab.classList.remove('active'));
    button?.classList.add('active');

    const url = button?.dataset?.serverUrl || '';
    if (url) {
        clearTimeout(streamStartTimer);
        streamAttemptId += 1;
        streamLoaded = false;
        streamLoading = null;
        try { vexioVideo?.pause?.(); } catch (_error) { }
        vexioVideo = null;
        clearPlayerUnavailable();
        setPlayerLoading(false);
        wrap?.classList.remove('is-ready');
        wrap?.classList.add('is-embed');
        target?.replaceChildren(Object.assign(document.createElement('iframe'), {
            className: 'vexio-embed-frame',
            src: url,
            title: button?.textContent?.trim() || 'External stream'
        }));
        const frame = target?.querySelector('.vexio-embed-frame');
        frame?.setAttribute('allowfullscreen', '');
        frame?.setAttribute('allow', 'autoplay; encrypted-media; fullscreen; picture-in-picture');
        frame?.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
    } else {
        target?.replaceChildren();
        streamLoaded = false;
        streamLoading = null;
        clearEmbedPlayer();
        initPlay();
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
