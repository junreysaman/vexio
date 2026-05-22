import { OMSSServer } from '@omss/framework';
import 'dotenv/config';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import { knownThirdPartyProxies } from './thirdPartyProxies.js';
import { streamPatterns } from './streamPatterns.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function main() {
    const server = new OMSSServer({
        name: 'CinePro',
        version: '1.0.0',

        // Network
        host: process.env.HOST ?? 'localhost',
        port: Number(process.env.PORT ?? 3000),
        publicUrl: process.env.PUBLIC_URL,

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
    applyProviderAllowlist(registry);
    registerFilteredSourceRoutes(server);

    await server.start();

    const publicUrl =
        process.env.PUBLIC_URL ??
        `http://${process.env.HOST ?? 'localhost'}:${process.env.PORT ?? 3000}`;

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

function applyProviderAllowlist(registry: ReturnType<OMSSServer['getRegistry']>) {
    const allowlist = (process.env.PROVIDER_ALLOWLIST ?? '')
        .split(',')
        .map((provider) => provider.trim().toLowerCase())
        .filter(Boolean);

    if (allowlist.length === 0) {
        return;
    }

    for (const providerId of registry.listProviders()) {
        if (!allowlist.includes(providerId.toLowerCase())) {
            registry.unregister(providerId);
        }
    }

    console.log(
        `[ProviderRegistry] Provider allowlist enabled: ${registry.listProviders().join(', ')}`
    );
}

function registerFilteredSourceRoutes(server: OMSSServer) {
    const app = server.getInstance();
    const sourceService = server as unknown as {
        sourceService: {
            tmdbValidator: {
                validateMovie(tmdbId: string): Promise<void>;
                validateTVEpisode(tmdbId: string, season: number, episode: number): Promise<void>;
            };
            tmdbService: {
                getMediaObject(
                    type: 'movie' | 'tv',
                    tmdbId: string,
                    season?: number,
                    episode?: number
                ): Promise<Record<string, unknown>>;
                getImdbId(tmdbId: string, type: 'movie' | 'tv'): Promise<string | null>;
            };
            buildResponse(results: Array<{ sources: unknown[]; subtitles: unknown[]; diagnostics: unknown[] }>): unknown;
        };
    };

    app.get('/v1/filtered/:providers/movies/:id', async (request, reply) => {
        const { providers, id } = request.params as { providers: string; id: string };
        await sourceService.sourceService.tmdbValidator.validateMovie(id);
        const media = await sourceService.sourceService.tmdbService.getMediaObject('movie', id);
        media.imdbId = (await sourceService.sourceService.tmdbService.getImdbId(id, 'movie')) ?? '';
        const results = await fetchFromSelectedProviders(server, 'movie', media, providers);
        return reply.code(200).send(sourceService.sourceService.buildResponse(results));
    });

    app.get('/v1/filtered/:providers/tv/:id/seasons/:s/episodes/:e', async (request, reply) => {
        const { providers, id, s, e } = request.params as {
            providers: string;
            id: string;
            s: string;
            e: string;
        };
        const season = parseInt(s, 10);
        const episode = parseInt(e, 10);
        await sourceService.sourceService.tmdbValidator.validateTVEpisode(id, season, episode);
        const media = await sourceService.sourceService.tmdbService.getMediaObject('tv', id, season, episode);
        media.imdbId = (await sourceService.sourceService.tmdbService.getImdbId(id, 'tv')) ?? '';
        const results = await fetchFromSelectedProviders(server, 'tv', media, providers);
        return reply.code(200).send(sourceService.sourceService.buildResponse(results));
    });
}

async function fetchFromSelectedProviders(
    server: OMSSServer,
    type: 'movie' | 'tv',
    media: Record<string, unknown>,
    providerParam: string
) {
    const selectedIds = providerParam
        .split(',')
        .map((provider) => provider.trim().toLowerCase())
        .filter(Boolean);
    const selected = new Set(selectedIds);
    const providers = server
        .getRegistry()
        .getProviders()
        .filter((provider) => selected.has(provider.id.toLowerCase()))
        .filter((provider) =>
            provider.capabilities.supportedContentTypes.includes(type === 'movie' ? 'movies' : 'tv')
        )
        .filter((provider) => provider.enabled);

    if (providers.length === 0) {
        return [];
    }

    const results = await Promise.allSettled(
        providers.map(async (provider) => {
            try {
                const result =
                    type === 'movie'
                        ? await provider.getMovieSources(media as never)
                        : await provider.getTVSources(media as never);

                console.log(
                    `[FilteredSource] Provider '${provider.name}' returned ${result.sources.length} source(s)`
                );

                return result;
            } catch (error) {
                console.error(`[FilteredSource] Provider '${provider.name}' failed:`, error);

                return {
                    sources: [],
                    subtitles: [],
                    diagnostics: [
                        {
                            code: 'PROVIDER_ERROR',
                            message: `Provider '${provider.name}' failed: ${
                                error instanceof Error ? error.message : 'Unknown error'
                            }`,
                            field: '',
                            severity: 'error'
                        }
                    ]
                };
            }
        })
    );

    return results
        .filter((result) => result.status === 'fulfilled')
        .map((result) => result.value);
}
