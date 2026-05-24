'use strict';

const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');

const REFERER = 'https://vidlink.pro/';
const ORIGIN  = 'https://vidlink.pro';
const UA      = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124';

// ── WASM singleton (survives warm invocations) ────────────────────────────────
let wasmReady = false;
let bootPromise = null;

function bootWasm() {
  if (bootPromise) return bootPromise;
  bootPromise = (async () => {
    globalThis.window = globalThis;
    globalThis.self = globalThis;
    globalThis.document = { createElement: () => ({}), body: { appendChild: () => {} } };

    const sodium = require('libsodium-wrappers');
    await sodium.ready;
    globalThis.sodium = sodium;

    eval(fs.readFileSync(path.join(__dirname, 'script.js'), 'utf8'));

    const go = new Dm();
    const wasmBuf = fs.readFileSync(path.join(__dirname, 'fu.wasm'));
    const { instance } = await WebAssembly.instantiate(wasmBuf, go.importObject);
    go.run(instance);

    await new Promise(r => setTimeout(r, 500));
    if (typeof globalThis.getAdv !== 'function') throw new Error('getAdv not found after WASM boot');
    wasmReady = true;
  })();
  return bootPromise;
}

// ── Stream URL resolver ───────────────────────────────────────────────────────
async function getStream(id, season, episode, options = {}) {
  await bootWasm();
  const token = globalThis.getAdv(String(id));
  if (!token) throw new Error('getAdv returned null');

  const multiLang = options.multiLang === false ? 0 : 1;
  const apiUrl = season
    ? `https://vidlink.pro/api/b/tv/${token}/${season}/${episode || 1}?multiLang=${multiLang}`
    : `https://vidlink.pro/api/b/movie/${token}?multiLang=${multiLang}`;

  const res = await fetch(apiUrl, {
    headers: { Referer: REFERER, Origin: ORIGIN, 'User-Agent': UA }
  });
  if (!res.ok) throw new Error(`vidlink API returned ${res.status}`);

  const raw = await res.text();
  let data;
  try {
    data = JSON.parse(raw);
  } catch (err) {
    throw new Error(`vidlink API returned invalid JSON (${res.status})`);
  }

  const playlist = data?.stream?.playlist;
  if (!playlist) throw new Error('No playlist in response');

  return {
    url: playlist,
    provider: data?.sourceId || 'vidlink',
    subtitles: collectSubtitles(data),
  };
}

function getAnimeEmbed(malId, episode, subOrDub = 'sub', fallback = true) {
  const normalizedId = Number.parseInt(String(malId || ''), 10);
  const normalizedEpisode = Math.max(1, Number.parseInt(String(episode || 1), 10) || 1);
  const mode = String(subOrDub || 'sub').toLowerCase() === 'dub' ? 'dub' : 'sub';

  if (!Number.isFinite(normalizedId) || normalizedId < 1) {
    throw new Error('missing MyAnimeList id');
  }

  return {
    embedUrl: `https://vidlink.pro/anime/${normalizedId}/${normalizedEpisode}/${mode}${fallback ? '?fallback=true' : ''}`,
    provider: 'vidlink-anime',
    requiresEmbed: true,
  };
}

function normalizeSubtitle(track) {
  if (!track || typeof track !== 'object') return null;

  const url = track.url || track.src || track.file || track.link;
  if (!url || typeof url !== 'string') return null;

  const label = track.label || track.name || track.language || track.lang || 'Subtitle';
  const lang = track.srclang || track.code || languageCode(track.lang) || languageCode(track.language) || 'und';

  return {
    url,
    label: String(label),
    lang: String(lang).slice(0, 12),
  };
}

function languageCode(language) {
  const normalized = String(language || '').trim().toLowerCase();
  const map = {
    arabic: 'ar',
    basque: 'eu',
    bulgarian: 'bg',
    catalan: 'ca',
    chinese: 'zh',
    croatian: 'hr',
    czech: 'cs',
    danish: 'da',
    dutch: 'nl',
    english: 'en',
    finnish: 'fi',
    french: 'fr',
    german: 'de',
    greek: 'el',
    hebrew: 'he',
    hindi: 'hi',
    hungarian: 'hu',
    indonesian: 'id',
    italian: 'it',
    japanese: 'ja',
    korean: 'ko',
    malay: 'ms',
    norwegian: 'no',
    polish: 'pl',
    portuguese: 'pt',
    romanian: 'ro',
    russian: 'ru',
    spanish: 'es',
    swedish: 'sv',
    thai: 'th',
    turkish: 'tr',
    ukrainian: 'uk',
    vietnamese: 'vi',
  };

  const key = Object.keys(map).find(name => normalized === name || normalized.startsWith(name + ' '));
  return key ? map[key] : '';
}

function collectSubtitles(data) {
  const candidates = [
    data?.subtitles,
    data?.subtitle,
    data?.captions,
    data?.tracks,
    data?.stream?.subtitles,
    data?.stream?.subtitle,
    data?.stream?.captions?.map?.(caption => ({
      ...caption,
      label: caption.label || caption.language,
    })),
    data?.stream?.tracks,
  ];

  return candidates
    .flatMap(value => Array.isArray(value) ? value : value ? [value] : [])
    .map(normalizeSubtitle)
    .filter(Boolean);
}

// ── HLS upstream fetcher with redirect support ────────────────────────────────
function fetchUpstream(url, redirects = 0) {
  return new Promise((resolve, reject) => {
    if (redirects > 5) return reject(new Error('too many redirects'));
    (url.startsWith('https') ? https : http).get(url, {
      headers: { Referer: REFERER, Origin: ORIGIN, 'User-Agent': UA, Accept: '*/*' }
    }, res => {
      if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        const loc = res.headers.location;
        return resolve(fetchUpstream(loc.startsWith('http') ? loc : new URL(loc, url).href, redirects + 1));
      }
      resolve(res);
    }).on('error', reject);
  });
}

function normalizeProxyUrl(value) {
  const url = new URL(value);
  if (!['http:', 'https:'].includes(url.protocol)) {
    throw new Error('unsupported proxy URL');
  }

  return url.href;
}

function rewriteM3u8(body, url) {
  const base = url.split('?')[0];
  const baseDir = base.substring(0, base.lastIndexOf('/') + 1);
  const origin = new URL(url).origin;
  const resolvePath = value => value.startsWith('http') ? value : value.startsWith('/') ? origin + value : baseDir + value;

  return body.split('\n').map(line => {
    const t = line.trim();
    if (!t) return line;
    if (t.startsWith('#')) {
      return line.replace(/URI="([^"]+)"/g, (_, value) => `URI="/api?url=${encodeURIComponent(resolvePath(value))}"`);
    }
    const abs = resolvePath(t);
    return '/api?url=' + encodeURIComponent(abs);
  }).join('\n');
}

// ── Vercel serverless handler ─────────────────────────────────────────────────
module.exports = async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');

  const { searchParams } = new URL(req.url, 'http://localhost');
  const q = Object.fromEntries(searchParams);

  // Proxy mode: /api?url=...
  if (q.url) {
    let url;
    try {
      url = normalizeProxyUrl(decodeURIComponent(q.url));
      const upstream = await fetchUpstream(url);
      const ct = (upstream.headers['content-type'] || '').toLowerCase();
      const isM3u8 = ct.includes('mpegurl') || ct.includes('m3u8') || /\.m3u8?(\?|$)/i.test(url.split('?')[0]);

      if (isM3u8) {
        const chunks = [];
        for await (const chunk of upstream) chunks.push(chunk);
        const body = Buffer.concat(chunks).toString('utf8');
        res.setHeader('Content-Type', 'application/vnd.apple.mpegurl');
        return res.end(rewriteM3u8(body, url));
      } else {
        const contentType = /\.vtt(\?|$)/i.test(url) ? 'text/vtt; charset=utf-8' : (ct || 'application/octet-stream');
        res.setHeader('Content-Type', contentType);
        if (upstream.headers['content-length']) res.setHeader('Content-Length', upstream.headers['content-length']);
        res.statusCode = upstream.statusCode;
        upstream.pipe(res);
      }
    } catch (err) {
      res.statusCode = 502;
      res.end(err.message);
    }
    return;
  }

  res.setHeader('Content-Type', 'application/json');
  try {
    if (q.type === 'anime' || q.anime || q.malId) {
      const stream = getAnimeEmbed(q.malId || q.id, q.number || q.e || 1, q.subOrDub || q.mode || 'sub', q.fallback !== 'false');
      return res.end(JSON.stringify(stream));
    }

    // Stream lookup: /api?id=550  or  /api?id=456&s=1&e=2
    if (!q.id) {
      res.statusCode = 400;
      return res.end(JSON.stringify({ error: 'missing id' }));
    }

    const stream = await getStream(q.id, q.s, q.e, {
      multiLang: q.multiLang !== '0',
    });
    res.end(JSON.stringify(stream));
  } catch (err) {
    res.statusCode = 500;
    res.end(JSON.stringify({ error: err.message }));
  }
};
