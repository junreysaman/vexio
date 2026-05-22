import { OMSSServer } from '@omss/framework';
import dotenv from 'dotenv';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import { knownThirdPartyProxies } from './thirdPartyProxies.js';
import { streamPatterns } from './streamPatterns.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

dotenv.config({ path: path.resolve(__dirname, '../../.env') });
dotenv.config({ path: path.resolve(__dirname, '../.env'), override: true });

function csvEnv(name: string): string[] {
    return (process.env[name] ?? '')
        .split(',')
        .map((value) => value.trim().toLowerCase())
        .filter(Boolean);
}

function withTimeout<T>(promise: Promise<T>, timeoutMs: number, label: string): Promise<T> {
    let timer: NodeJS.Timeout;
    const timeout = new Promise<never>((_, reject) => {
        timer = setTimeout(
            () => reject(new Error(`${label} timed out after ${timeoutMs}ms`)),
            timeoutMs
        );
    });

    return Promise.race([promise, timeout]).finally(() => clearTimeout(timer));
}

function tuneProviders(registry: ReturnType<OMSSServer['getRegistry']>) {
    const enabledProviderIds = csvEnv('CINEPRO_ENABLED_PROVIDERS');
    if (enabledProviderIds.length > 0) {
        const enabled = new Set(enabledProviderIds);
        for (const provider of registry.getProviders()) {
            if (!enabled.has(provider.id.toLowerCase())) {
                registry.unregister(provider.id);
            }
        }
    }

    const timeoutMs = Math.max(1000, Number(process.env.CINEPRO_PROVIDER_TIMEOUT_MS ?? 6500));
    for (const provider of registry.getProviders()) {
        const originalMovieSources = provider.getMovieSources.bind(provider);
        provider.getMovieSources = (media) =>
            withTimeout(originalMovieSources(media), timeoutMs, provider.name);

        const originalTVSources = provider.getTVSources.bind(provider);
        provider.getTVSources = (media) =>
            withTimeout(originalTVSources(media), timeoutMs, provider.name);
    }
}

async function main() {
    const server = new OMSSServer({
        name: 'CinePro',
        version: '1.0.0',

        // Network
        host: process.env.HOST ?? '127.0.0.1',
        port: Number(process.env.PORT ?? 3020),
        publicUrl: process.env.PUBLIC_URL ?? process.env.CINEPRO_CORE_URL,

        // Cache (memory for dev, Redis for prod)
        cache: {
            type: (process.env.CACHE_TYPE as 'memory' | 'redis') ?? 'memory',
            ttl: {
                sources: 60 * 60,
                subtitles: 60 * 60 * 24
            },
            redis: {
                host: process.env.REDIS_HOST ?? 'localhost',
                port: Number(process.env.REDIS_PORT ?? 6379),
                password: process.env.REDIS_PASSWORD
            }
        },

        // TMDB
        tmdb: {
            apiKey: process.env.TMDB_API_KEY!,
            cacheTTL: 24 * 60 * 60 // 24h
        },

        // Third Party Proxy removal
        proxyConfig: {
            knownThirdPartyProxies: knownThirdPartyProxies,
            streamPatterns
        },

        cors: {
            origin: process.env.CORS_ORIGIN ?? '*',
            methods: ['GET', 'OPTIONS'],
            allowedHeaders: ['Content-Type', 'Authorization'],
            exposedHeaders: ['Content-Range', 'Accept-Ranges', 'ETag'],
            preflightContinue: false,
            optionsSuccessStatus: 204
        },

        stremio: {
            // exposes a stremio addon on /stremio/manifest.json
            enableNativeAddon: process.env.STREMIO_ADDON === 'true',
            // you can your own custom stremio addons as sources into cinepro.
            stremioAddons: []
            /*
            stremioAddons: [
                {
                    id: 'some-unique-id',
                    url: 'https://example.com/manifest.json',
                    enabled: true
                }
            ]
            */
        },

        // MCP for AI agents
        mcp: {
            enabled: process.env.MCP_ENABLED === 'true'
        }
    });

    // Register providers
    const registry = server.getRegistry();
    await registry.discoverProviders(path.join(__dirname, './providers/'));
    tuneProviders(registry);

    await server.start();

    const publicUrl =
        process.env.PUBLIC_URL ??
        process.env.CINEPRO_CORE_URL ??
        `http://${process.env.HOST ?? '127.0.0.1'}:${process.env.PORT ?? 3020}`;

    const uiUrl = `https://ui.cinepro.cc/?omssurl=${encodeURIComponent(publicUrl)}`;

    const title = '🚀 CinePro/ui is in public testing';
    const contrib =
        '🤝 We are looking for contributors to improve and develop!';
    const repo = 'Contribute: https://github.com/cinepro-org/ui';
    const tryIt = `🌐 Try it out: ${uiUrl} !`;
    const note =
        'You will need to give the website "access to local applications" that it works.';

    const lines = [title, '', repo, '', contrib, '', tryIt, '', note];

    // compute box width based on longest line
    const width = Math.max(...lines.map((l) => l.length)) + 2;

    const borderTop = '╭' + '─'.repeat(width) + '╮';
    const borderBottom = '╰' + '─'.repeat(width) + '╯';

    const pad = (line: string) => '│ ' + line.padEnd(width - 2, ' ') + ' │';

    console.log(`
================== CINEPRO BETA ANNOUNCEMENT ==================

${borderTop}
${lines.map(pad).join('\n')}
${borderBottom}
`);
}

main().catch(() => {
    process.exit(1);
});
