const crypto = require('crypto');
const axios = require('axios');

const BASE_URL = 'https://core.vidzee.wtf';
const PLAYER_URL = 'https://player.vidzee.wtf';
const VIDZEE_HEADERS = {
    'User-Agent': 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.7051.98 Safari/537.36',
    'Accept': 'application/json, text/javascript, */*; q=0.01',
    'Accept-Language': 'en-US,en;q=0.9',
    'Referer': PLAYER_URL,
    'Origin': PLAYER_URL
};

// Pad/truncate key string to 32 bytes (UTF-8, mirrors CryptoJS behaviour)
function getKeyBytes(key) {
    const encoded = Buffer.from(key, 'utf8');
    const result = Buffer.alloc(32);
    encoded.copy(result, 0, 0, Math.min(encoded.length, 32));
    return result;
}

// AES-256-CBC decrypt a VidZee stream link.
// encryptedData is outer-base64 of "ivBase64:cipherBase64"
async function decryptLink(encryptedData, decryptionKey) {
    try {
        if (!encryptedData || !decryptionKey) return '';

        const decoded = Buffer.from(encryptedData, 'base64').toString('utf8');
        const colonIdx = decoded.indexOf(':');
        if (colonIdx === -1) return '';

        const ivBase64 = decoded.slice(0, colonIdx);
        const cipherBase64 = decoded.slice(colonIdx + 1);
        if (!ivBase64 || !cipherBase64) return '';

        const iv = Buffer.from(ivBase64, 'base64');
        const cipherBytes = Buffer.from(cipherBase64, 'base64');
        const keyBytes = getKeyBytes(decryptionKey);

        const decipher = crypto.createDecipheriv('aes-256-cbc', keyBytes, iv);
        const decrypted = Buffer.concat([decipher.update(cipherBytes), decipher.final()]);
        return decrypted.toString('utf8');
    } catch {
        return '';
    }
}

// Derive the real AES-CBC decryption key from VidZee's API-key blob via AES-GCM.
// Mirrors the TypeScript deriveKey() in the reference implementation.
async function deriveKey(e) {
    try {
        if (!e) return '';

        const t = Buffer.from(e.replace(/\s+/g, ''), 'base64');
        if (t.length <= 28) return '';

        const n = t.slice(0, 12);   // AES-GCM IV
        const r = t.slice(12, 28);
        const a = t.slice(28);

        // i = a || r  (reference: i.set(a, 0); i.set(r, a.length))
        const i = Buffer.concat([a, r]);

        // AES-256-GCM key = SHA-256 of fixed salt string
        const l = crypto.createHash('sha256')
            .update('4f2a9c7d1e8b3a6f0d5c2e9a7b1f4d8c', 'utf8')
            .digest();

        // WebCrypto passes ciphertext+tag as one buffer; tag is last 16 bytes
        const ciphertext = i.slice(0, i.length - 16);
        const authTag = i.slice(i.length - 16);

        const decipher = crypto.createDecipheriv('aes-256-gcm', l, n);
        decipher.setAuthTag(authTag);
        const decrypted = Buffer.concat([decipher.update(ciphertext), decipher.final()]);
        return decrypted.toString('utf8');
    } catch {
        return '';
    }
}

// Fetch and derive the current decryption key from VidZee's API
async function fetchDecryptionKey() {
    try {
        const response = await axios.get(`${BASE_URL}/api-key`, {
            headers: VIDZEE_HEADERS,
            responseType: 'text',
            timeout: 10000
        });
        if (response.status === 200 && response.data) {
            return await deriveKey(response.data);
        }
        return null;
    } catch {
        return null;
    }
}

const getVidZeeStreams = async (tmdbId, mediaType, seasonNum, episodeNum) => {
    if (!tmdbId) {
        console.error('[VidZee] Error: TMDB ID (tmdbId) is required.');
        return [];
    }

    if (!mediaType || (mediaType !== 'movie' && mediaType !== 'tv')) {
        console.error('[VidZee] Error: mediaType is required and must be either "movie" or "tv".');
        return [];
    }

    if (mediaType === 'tv') {
        if (!seasonNum) {
            console.error('[VidZee] Error: Season (seasonNum) is required for TV shows.');
            return [];
        }
        if (!episodeNum) {
            console.error('[VidZee] Error: Episode (episodeNum) is required for TV shows.');
            return [];
        }
    }

    // Fetch and derive the decryption key before hitting servers
    const decKey = await fetchDecryptionKey();
    if (!decKey) {
        console.error('[VidZee] Failed to fetch/derive decryption key.');
        return [];
    }
    console.log('[VidZee] Decryption key derived successfully.');

    // Servers 0–13 (matches reference implementation)
    const servers = Array.from({ length: 14 }, (_, i) => i);

    const streamPromises = servers.map(async (sr) => {
        let apiUrl = `${PLAYER_URL}/api/server?id=${tmdbId}&sr=${sr}`;
        if (mediaType === 'tv') {
            apiUrl += `&ss=${seasonNum}&ep=${episodeNum}`;
        }

        try {
            const response = await axios.get(apiUrl, {
                headers: VIDZEE_HEADERS,
                timeout: 7000
            });

            const responseData = response.data;
            if (!responseData || typeof responseData !== 'object') return [];

            let apiSources = [];
            if (responseData.url && Array.isArray(responseData.url)) {
                apiSources = responseData.url;
            } else if (responseData.link && typeof responseData.link === 'string') {
                apiSources = [responseData];
            }

            if (apiSources.length === 0) return [];

            const streams = await Promise.all(apiSources.map(async (sourceItem) => {
                const label = sourceItem.name || sourceItem.type || 'VidZee';
                let quality = String(label).match(/^\d+$/) ? `${label}p` : label;
                if (!/\d{3,4}p/.test(quality)) quality = '720p';
                const language = sourceItem.language || sourceItem.lang || 'Unknown';

                let rawLink = sourceItem.link;
                if (rawLink && !/^https?:\/\//i.test(rawLink)) {
                    const decoded = await decryptLink(rawLink, decKey);
                    if (decoded && /^https?:\/\//i.test(decoded)) {
                        rawLink = decoded;
                    } else {
                        console.log(`[VidZee S${sr}] Decryption yielded non-URL, skipping.`);
                        return null;
                    }
                }

                if (!rawLink || !/^https?:\/\//i.test(rawLink)) return null;

                return {
                    name: `VidZee Server${sr} - ${quality} - ${language}`,
                    title: `VidZee Server${sr} - ${quality} - ${language}`,
                    url: rawLink,
                    quality,
                    provider: 'VidZee',
                    headers: { 'Referer': `${BASE_URL}/` }
                };
            }));

            const valid = streams.filter(Boolean);
            if (valid.length > 0) {
                console.log(`[VidZee S${sr}] ${valid.length} stream(s) extracted.`);
            }
            return valid;
        } catch (error) {
            console.error(`[VidZee S${sr}] Error: ${error.message}`);
            return [];
        }
    });

    const allStreams = (await Promise.all(streamPromises)).flat();
    console.log(`[VidZee] Total streams: ${allStreams.length}`);
    return allStreams;
};

module.exports = { getVidZeeStreams };
