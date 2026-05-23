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

        rows.forEach(row => { row.hidden = true; });
        matches.slice(0, visibleLimit).forEach(row => { row.hidden = false; });

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
        rows.forEach((row, index) => { row.hidden = index >= visibleLimit; });
        if (sentinel) sentinel.hidden = visibleLimit >= rows.length;
    };

    const revealMore = () => {
        if (visibleLimit >= rows.length) return;
        visibleLimit = Math.min(rows.length, visibleLimit + pageSize);
        render();
    };

    if ('IntersectionObserver' in window && sentinel) {
        const observer = new IntersectionObserver(entries => {
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

let vexioVideo = null;
let streamLoaded = false;
let streamLoading = null;
let streamSources = [];
let streamSourceIndex = 0;
let streamStartTimer = null;
let streamAttemptId = 0;
let vidstackModule = null;
let tvCountdownTimer;

function initProviderSwitch(sources) {
    const wrap = document.getElementById('playerWrap');
    const providerSwitch = document.getElementById('vexioProviderSwitch');
    const menu = document.getElementById('vexioProviderSwitchMenu');
    const value = document.getElementById('vexioProviderSwitchValue');
    if (!providerSwitch || !menu) return;

    if (!Array.isArray(sources) || sources.length < 1) {
        providerSwitch.hidden = true;
        return;
    }

    // If only 1 source, hide
    if (sources.length === 1) {
        providerSwitch.hidden = true;
        return;
    }

    providerSwitch.hidden = false;
    menu.hidden = false;
    menu.innerHTML = '';

    const escapeHtml = (v) => String(v ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '<')
        .replace(/>/g, '>')
        .replace(/"/g, '"')
        .replace(/'/g, '&#039;');

    // Build dropdown items: provider -> quality -> select by source index
    const grouped = new Map();
    sources.forEach((s, idx) => {
        const provider = s?.provider?.name || s?.provider?.id || 'Unknown';
        const quality = s?.quality || '';
        const type = s?.type || '';
        const key = `${provider}||${quality}||${type}`;
        if (!grouped.has(key)) grouped.set(key, { provider, quality, type, items: [] });
        grouped.get(key).items.push({ idx });
    });

    // sort by provider then quality
    const keys = Array.from(grouped.keys()).sort((a, b) => {
        const ga = grouped.get(a);
        const gb = grouped.get(b);
        return String(ga.provider).localeCompare(String(gb.provider));
    });

    keys.forEach(key => {
        const g = grouped.get(key);
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'vexio-provider-switch-item';

        const label = `${g.provider}${g.quality ? ' • ' + g.quality : ''}${g.type ? ' • ' + g.type : ''}`;
        item.innerHTML = `<div class="vexio-provider-switch-item-label">${escapeHtml(label)}</div>`;

        // use first source index for this provider/quality/type group
        const sourceIdx = g.items[0]?.idx;
        item.onclick = () => {
            streamSourceIndex = sourceIdx;
            if (value) {
                value.textContent = label;
            }
            providerSwitch.hidden = true;
            menu.hidden = true;
            // reload from selected source
            streamLoaded = false;
            streamLoading = null;
            clearTimeout(streamStartTimer);
            Promise.resolve(loadVexioStream(true)).catch(() => { });
        };

        menu.appendChild(item);
    });

    // toggle menu on click of toggle button
    const toggleBtn = document.getElementById('vexioProviderSwitchToggle');
    if (toggleBtn) {
        toggleBtn.onclick = () => {
            const isHidden = menu.hidden;
            menu.hidden = !isHidden;
        };
    }

    if (value) value.textContent = 'Auto';
}

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

function createVidstackPlayer(source, subtitles) {

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

    player.addEventListener('ended', triggerNextEp);
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
        // add intermediate rendering state and wait for actual media frame
        wrap.classList.add('is-player-rendering');

        const target = document.getElementById('vexioPlayerTarget');
        const finalizeReady = () => {
            if (attemptId !== streamAttemptId) return;
            wrap.classList.remove('is-player-rendering');
            wrap.classList.add('is-ready');
            setPlayerLoading(false);
            if (shouldPlay) Promise.resolve(player.play()).catch(() => { });
        };

        const videoEl = target?.querySelector('video');
        if (videoEl) {
            if (videoEl.readyState >= 3 || videoEl.videoWidth > 0) {
                finalizeReady();
            } else {
                const onRendered = () => { videoEl.removeEventListener('playing', onRendered); videoEl.removeEventListener('loadeddata', onRendered); finalizeReady(); };
                videoEl.addEventListener('playing', onRendered, { once: true });
                videoEl.addEventListener('loadeddata', onRendered, { once: true });
                // fallback in case events don't fire
                setTimeout(finalizeReady, 400);
            }
        } else {
            // fallback: check a few animation frames for a video element
            let checks = 0;
            const checkLoop = () => {
                const v = target?.querySelector('video');
                if (v && (v.readyState >= 3 || v.videoWidth > 0)) return finalizeReady();
                checks += 1;
                if (checks < 6) return requestAnimationFrame(checkLoop);
                setTimeout(finalizeReady, 200);
            };
            requestAnimationFrame(checkLoop);
        }
    };
    const tryNextSource = async () => {
        clearTimeout(streamStartTimer);
        if (streamLoaded || attemptId !== streamAttemptId) return;
        const nextSource = streamSources[streamSourceIndex + 1];
        if (!nextSource) {
            setPlayerUnavailable('No playable source found for this episode');
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

            // Provider/quality switcher UI
            initProviderSwitch(streamSources);

            const source = streamSources[streamSourceIndex];
            if (!source) {
                setPlayerUnavailable('No playable source found for this episode');
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

    // Prevent audio warning flash when switching servers
    const audioWarn = document.getElementById('vexioAudioUnavailable');
    if (audioWarn) audioWarn.hidden = true;
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
        initVexioVideo();
        loadVexioStream(false);
    });
} else {
    initEpisodeList();
    initSidebarEpisodeList();
    initVexioVideo();
    loadVexioStream(false);
}
