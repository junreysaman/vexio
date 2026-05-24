let userRating = 0;
let vexioVideo = null;
let vexioVideoJsPlayer = null;
let vexioVideoElement = null;
let streamLoaded = false;
let streamLoading = null;
let streamSources = [];
let streamSourceIndex = 0;
let streamStartTimer = null;
let streamAttemptId = 0;

function initProviderSwitch(sources) {
    const providerSwitch = document.getElementById('vexioProviderSwitch');
    const menu = document.getElementById('vexioProviderSwitchMenu');
    const value = document.getElementById('vexioProviderSwitchValue');
    if (!providerSwitch || !menu) return;

    if (!Array.isArray(sources) || sources.length < 1) {
        providerSwitch.hidden = true;
        return;
    }

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

    const grouped = new Map();
    sources.forEach((s, idx) => {
        const provider = s?.provider?.name || s?.provider?.id || 'Unknown';
        const quality = s?.quality || '';
        const type = s?.type || '';
        const key = `${provider}||${quality}||${type}`;
        if (!grouped.has(key)) grouped.set(key, { provider, quality, type, items: [] });
        grouped.get(key).items.push({ idx });
    });

    const keys = Array.from(grouped.keys()).sort((a, b) => {
        const ga = grouped.get(a);
        const gb = grouped.get(b);
        return String(ga.provider).localeCompare(String(gb.provider));
    });

    keys.forEach(key => {
        const g = grouped.get(key);

        const label = `${g.provider}${g.quality ? ' • ' + g.quality : ''}${g.type ? ' • ' + g.type : ''}`;
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'vexio-provider-switch-item';
        item.innerHTML = `<div class="vexio-provider-switch-item-label">${escapeHtml(label)}</div>`;

        const sourceIdx = g.items[0]?.idx;
        item.onclick = () => {
            streamSourceIndex = sourceIdx;
            if (value) value.textContent = label;

            providerSwitch.hidden = true;
            menu.hidden = true;

            streamLoaded = false;
            streamLoading = null;
            clearTimeout(streamStartTimer);
            Promise.resolve(loadVexioStream(true)).catch(() => { });
        };

        menu.appendChild(item);
    });

    const toggleBtn = document.getElementById('vexioProviderSwitchToggle');
    if (toggleBtn) {
        toggleBtn.onclick = () => {
            menu.hidden = !menu.hidden;
        };
    }

    if (value) value.textContent = 'Auto';
}

function initVexioVideo() {
    if (vexioVideo) return vexioVideo;
    return document.querySelector('#vexioPlayerTarget video');
}

function disposeVexioPlayer() {
    if (vexioVideoJsPlayer && typeof vexioVideoJsPlayer.dispose === 'function') {
        try {
            vexioVideoJsPlayer.dispose();
        } catch (_error) { }
    }

    vexioVideoJsPlayer = null;
    vexioVideoElement = null;
    vexioVideo = null;
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

function createVideoTracks(subtitles) {
    const tracks = normalizeSubtitles(subtitles);
    const defaultIndex = Math.max(0, tracks.findIndex(isEnglishSubtitle));
    return tracks.map((subtitle, index) => {
        const track = document.createElement('track');
        track.src = subtitle.url;
        track.kind = 'subtitles';
        track.label = subtitle.label;
        track.srclang = subtitleLanguage(subtitle);
        track.type = 'text/vtt';
        if (index === defaultIndex) track.default = true;
        return track;
    });
}

function normalizeAudioTracks(audioTracks) {
    if (!Array.isArray(audioTracks)) return [];

    const seen = new Set();
    return audioTracks
        .filter(t => t && typeof t === 'object')
        .map(t => {
            const language = String(t.language || t.lang || '').trim();
            const label = String(t.label || t.language || t.lang || 'Audio').trim();
            const url = String(t.url || t.src || t.file || '').trim();
            return { language, label, url };
        })
        .filter(t => {
            const key = `${t.language}|${t.label}|${t.url || 'no-url'}`.toLowerCase();
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        });
}

function inferDubSubModeFromTracks(audioTracks) {
    // Best-effort: if we have one track, assume it's the dubbed/original that the provider selected.
    // If labels contain "original" => treat as original/subbed; otherwise treat as dubbed.
    const tracks = normalizeAudioTracks(audioTracks);
    if (tracks.length === 0) return null;

    const labels = tracks.map(t => String(t.label || '').toLowerCase());

    const hasOriginal = labels.some(l => l.includes('original'));
    const hasDub = labels.some(l => l.includes('dub') || l.includes('dubbed'));

    if (hasDub) return 'dubbed';
    if (hasOriginal) return 'original';
    return tracks.length === 1 ? 'dubbed' : 'dubbed';
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

function isTrustedEmbedOrigin(origin) {
    const endpoint = document.getElementById('playerWrap')?.dataset.playerSourceUrl || '';
    try {
        return Boolean(endpoint) && new URL(endpoint, window.location.href).origin === origin;
    } catch (_error) {
        return false;
    }
}

function handleEmbedMessage(event) {
    if (!isTrustedEmbedOrigin(event.origin)) return;
    if (event.data?.type === 'vexio:embed-ready') {
        const wrap = document.getElementById('playerWrap');
        wrap?.classList.remove('is-player-rendering');
        wrap?.classList.add('is-ready');
        setPlayerLoading(false);
        return;
    }

    if (event.data?.type !== 'vexio:embed-error') return;
    setPlayerUnavailable(event.data.message || 'No playable source found on this server. Try another server below.');
}

async function createNativePlayer(source, subtitles) {
    const wrap = document.getElementById('playerWrap');
    const target = document.getElementById('vexioPlayerTarget');
    if (!wrap || !target || !source?.url) return null;

    disposeVexioPlayer();
    target.replaceChildren();

    const video = document.createElement('video');
    video.className = 'video-js vjs-big-play-centered vjs-theme-vexio';
    video.controls = true;
    video.preload = 'metadata';
    video.playsInline = true;
    video.crossOrigin = 'anonymous';
    video.poster = target.dataset.poster || '';
    video.setAttribute('aria-label', target.dataset.title || wrap.dataset.title || 'Video player');
    video.setAttribute('data-setup', '{}');
    video.style.width = '100%';
    video.style.height = '100%';

    const sourceEl = document.createElement('source');
    sourceEl.src = source.url;
    sourceEl.type = sourceMimeType(source);
    video.appendChild(sourceEl);

    const tracks = createVideoTracks([...(subtitles || []), ...(source.subtitles || []), ...(source.tracks || [])]);
    tracks.forEach(track => video.appendChild(track));

    const audioTracks = normalizeAudioTracks(source?.audioTracks || []);
    const dubMode = inferDubSubModeFromTracks(source?.audioTracks || []);

    if (wrap) {
        wrap.dataset.dubSubMode = dubMode || '';
        wrap.dataset.audioTrackCount = String(audioTracks.length);
    }

    vexioVideo = video;
    vexioVideoElement = video;
    target.appendChild(video);

    if (window.videojs && typeof window.videojs === 'function') {
        try {
            vexioVideoJsPlayer = window.videojs(video, {
                controls: true,
                fluid: true,
                autoplay: false,
                preload: 'metadata',
                playbackRates: [1, 1.25, 1.5, 2],
                controlBar: {
                    pictureInPictureToggle: true,
                    fullscreenToggle: true
                }
            });
        } catch (_error) {
            vexioVideoJsPlayer = null;
        }
    }
    // Attach HLS fallback when needed (non-Safari browsers)
    try {
        console.log('[Vexio Player] createNativePlayer', { url: source?.url, type: source?.type });
        const isHls = String(source?.type || '').toLowerCase() === 'hls' || String(source?.url || '').toLowerCase().includes('.m3u8');
        const canPlayHlsNatively = video.canPlayType('application/vnd.apple.mpegurl');
        if (isHls && !canPlayHlsNatively) {
            if (window.Hls && typeof window.Hls === 'function') {
                try {
                    const hls = new window.Hls();
                    hls.attachMedia(video);
                    hls.loadSource(source.url);
                    // store reference for possible teardown
                    video._vexio_hls = hls;
                } catch (err) {
                    console.warn('[Vexio Player] Hls attach failed', err);
                }
            } else {
                console.warn('[Vexio Player] Hls.js not available for non-native HLS playback');
            }
        }
    } catch (err) {
        console.warn('[Vexio Player] createNativePlayer error', err);
    }

    video.addEventListener('error', (ev) => {
        console.error('[Vexio Player] video error event', ev, video.error);
    });
    return video;
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
        player = await createNativePlayer(source, subtitles);
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
        // This prevents flicker in production where the browser may satisfy readyState/loadeddata early.
        const finalizeReady = () => {
            if (attemptId !== streamAttemptId) return;
            wrap.classList.remove('is-player-rendering');
            wrap.classList.add('is-ready');
            setPlayerLoading(false);

            // Best-effort audio presence detection for provider streams that contain no audio.
            // The rendered <video> element is the source of truth for audio availability.
            try {
                const videoEl = target?.querySelector('video');
                if (videoEl) {
                    const markAudio = (available) => {
                        if (attemptId !== streamAttemptId) return;
                        wrap.dataset.audioAvailable = available ? '1' : '0';
                    };

                    // If browser exposes muted/volume and duration signal, audio likely exists.
                    // This is not perfect, but helps you detect the common "silent" case.
                    const isProbablySilent = () => {
                        try {
                            // If media is renderable but still has no audio, muted may remain true
                            // and/or audio tracks may not be reported.
                            // We treat it as silent if volume is 0 and muted and readyState is present.
                            return (videoEl.muted === true) && (Number(videoEl.volume) === 0);
                        } catch (_e) {
                            return false;
                        }
                    };

                    // Wait a tick for HLS to attach audio buffers.
                    setTimeout(() => {
                        // If audio buffers never appear, duration may still exist.
                        // Use heuristic: if not muted and volume > 0, treat as available.
                        const available = (() => {
                            try {
                                if (videoEl.muted === true && Number(videoEl.volume) === 0) return false;
                                return true;
                            } catch (_e) {
                                return true;
                            }
                        })();

                        // additional heuristic: if our silent heuristic says silent, override.
                        if (isProbablySilent()) return markAudio(false);
                        markAudio(available);

                        // show/hide warning UI
                        const audioWarn = document.getElementById('vexioAudioUnavailable');
                        if (audioWarn) audioWarn.hidden = !available;

                    }, 1200);
                }
            } catch (_e) { }

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
            // Wait until the native <video> element becomes available.
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
            setPlayerUnavailable('No playable source found on this server. Try another server below.');
            return;
        }
        streamSourceIndex += 1;
        setPlayerLoading(true, 'Trying vexio-main fallback');
        await playSource(nextSource, subtitles, shouldPlay);
    };

    player.addEventListener('canplay', startPlayback, { once: true });
    player.addEventListener('loadeddata', startPlayback, { once: true });
    player.addEventListener('error', tryNextSource, { once: true });

    // If the underlying <video> fails (common when proxy/manifest fetch is flaky),
    // switch to the next candidate immediately.
    const targetVideo = target?.querySelector('video');
    if (targetVideo) {
        targetVideo.addEventListener('error', tryNextSource, { once: true });
    }

    // Short trial window: if the video never reaches a renderable state,
    // don't wait the full 15s; switch sources to avoid "no playable source found" on first attempt.
    streamStartTimer = setTimeout(tryNextSource, 10000);

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
    if (!endpoint) {
        setPlayerUnavailable('No playable source configured for this movie');
        return false;
    }

    if (/^https?:\/\//i.test(endpoint)) {
        const wrap = document.getElementById('playerWrap');
        const target = document.getElementById('vexioPlayerTarget');
        clearTimeout(streamStartTimer);
        streamAttemptId += 1;
        streamLoaded = false;
        streamLoading = null;
        try { vexioVideo?.pause?.(); } catch (_error) { }
        vexioVideo = null;
        clearPlayerUnavailable();
        setPlayerLoading(true, 'Preparing VEXIO player');
        wrap?.classList.remove('is-ready');
        wrap?.classList.add('is-embed', 'is-player-rendering');
        target?.replaceChildren(Object.assign(document.createElement('iframe'), {
            className: 'vexio-embed-frame',
            src: endpoint,
            title: 'Embedded stream'
        }));
        const frame = target?.querySelector('.vexio-embed-frame');
        frame?.setAttribute('allowfullscreen', '');
        frame?.setAttribute('allow', 'autoplay; encrypted-media; fullscreen; picture-in-picture');
        frame?.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
        frame?.addEventListener('load', () => {
            setPlayerLoading(true, 'Waiting for playable source');
        }, { once: true });
        return true;
    }

    streamLoading = (async () => {
        try {
            setPlayerLoading(true, 'Scanning servers');
            clearPlayerUnavailable();
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
            const data = await response.json();

            // Debug logging
            console.log('[Vexio Player] API Response:', { endpoint, status: response.status, ok: response.ok, data });

            if (!response.ok || data.error) throw new Error(data.error?.message || 'Stream request failed');

            const allSources = Array.isArray(data.sources) ? data.sources : [];
            console.log('[Vexio Player] Total sources from API:', allSources.length);

            streamSources = allSources
                .filter(item => {
                    const hasUrl = !!item?.url;
                    const isBrowserPlayable = item.browserPlayable !== false;
                    console.log('[Vexio Player] Source filter:', {
                        provider: item?.provider?.name,
                        quality: item?.quality,
                        type: item?.type,
                        hasUrl,
                        browserPlayable: item?.browserPlayable,
                        passes: hasUrl && isBrowserPlayable
                    });
                    return hasUrl && isBrowserPlayable;
                });

            console.log('[Vexio Player] Filtered sources (playable):', streamSources.length);
            streamSourceIndex = 0;

            // Provider/quality switcher UI
            initProviderSwitch(streamSources);

            const source = streamSources[streamSourceIndex];
            if (!source) {
                setPlayerUnavailable('No playable source found on this server. Try another server below.');
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
        setPlayerLoading(button?.dataset?.serverKey === 'vexio-embed', 'Preparing VEXIO player');
        wrap?.classList.remove('is-ready');
        wrap?.classList.add('is-embed');
        wrap?.classList.toggle('is-player-rendering', button?.dataset?.serverKey === 'vexio-embed');
        target?.replaceChildren(Object.assign(document.createElement('iframe'), {
            className: 'vexio-embed-frame',
            src: url,
            title: button?.textContent?.trim() || 'External stream'
        }));
        const frame = target?.querySelector('.vexio-embed-frame');
        frame?.setAttribute('allowfullscreen', '');
        frame?.setAttribute('allow', 'autoplay; encrypted-media; fullscreen; picture-in-picture');
        frame?.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
        if (button?.dataset?.serverKey === 'vexio-embed') {
            frame?.addEventListener('load', () => {
                setPlayerLoading(true, 'Waiting for playable source');
            }, { once: true });
        }
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
        window.addEventListener('message', handleEmbedMessage);
        initVexioVideo();
        loadVexioStream(false);
    });
} else {
    window.addEventListener('message', handleEmbedMessage);
    initVexioVideo();
    loadVexioStream(false);
}

// Debugging helpers: surface uncaught errors and unhandled rejections to console/UI
window.addEventListener('error', (ev) => {
    try {
        const err = ev.error || ev.message || ev;
        console.error('[Vexio Player] Uncaught error:', err, ev.filename, ev.lineno, ev.colno);
        if (err && err.stack) console.error(err.stack);
        // show concise message in player UI
        setPlayerUnavailable(String(err?.message || err || 'Uncaught error'));
    } catch (_e) { console.error(_e); }
});

window.addEventListener('unhandledrejection', (ev) => {
    try {
        const reason = ev.reason || ev;
        console.error('[Vexio Player] Unhandled rejection:', reason);
        if (reason && reason.stack) console.error(reason.stack);
        setPlayerUnavailable(String(reason?.message || reason || 'Unhandled promise rejection'));
    } catch (_e) { console.error(_e); }
});
