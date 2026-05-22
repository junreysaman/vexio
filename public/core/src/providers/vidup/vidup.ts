import { BaseProvider } from '@omss/framework';
import type {
    ProviderCapabilities,
    ProviderMediaObject,
    ProviderResult,
    Source,
    Subtitle,
    SubtitleFormat
} from '@omss/framework';

interface WyzieSubtitle {
    display?: string;
    language?: string;
    url?: string;
    encoding?: string;
}

export class VidUpProvider extends BaseProvider {
    readonly id = 'vidup';
    readonly name = 'VidUp';
    readonly enabled = true;
    readonly BASE_URL = 'https://vidup.to';
    readonly HEADERS = {
        'User-Agent':
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/150 Safari/537.36',
        Accept: 'application/json, text/plain, */*',
        'Accept-Language': 'en-US,en;q=0.9',
        Referer: `${this.BASE_URL}/`,
        Origin: this.BASE_URL
    };

    readonly capabilities: ProviderCapabilities = {
        supportedContentTypes: ['movies', 'tv']
    };

    async getMovieSources(media: ProviderMediaObject): Promise<ProviderResult> {
        return this.getSources(media);
    }

    async getTVSources(media: ProviderMediaObject): Promise<ProviderResult> {
        return this.getSources(media);
    }

    private async getSources(
        media: ProviderMediaObject
    ): Promise<ProviderResult> {
        const embedUrl = this.buildEmbedUrl(media);
        const sources: Source[] = [
            {
                url: embedUrl,
                type: 'embed',
                quality: 'Auto',
                audioTracks: [{ language: 'en', label: 'English' }],
                provider: { id: this.id, name: this.name }
            }
        ];

        return {
            sources,
            subtitles: await this.fetchSubtitles(media, embedUrl),
            diagnostics: [
                {
                    code: 'PARTIAL_SCRAPE',
                    message:
                        'VidUp direct HLS/MP4 extraction is protected by an in-browser VM; provider currently exposes embed plus subtitles.',
                    field: 'sources',
                    severity: 'warning'
                }
            ]
        };
    }

    private buildEmbedUrl(media: ProviderMediaObject): string {
        const path =
            media.type === 'tv'
                ? `/tv/${media.tmdbId}/${media.s ?? 1}/${media.e ?? 1}`
                : `/movie/${media.tmdbId}`;

        const params = new URLSearchParams({
            autoPlay: 'true',
            nextButton: 'false',
            title: 'false'
        });

        return `${this.BASE_URL}${path}?${params.toString()}`;
    }

    private async fetchSubtitles(
        media: ProviderMediaObject,
        referer: string
    ): Promise<Subtitle[]> {
        try {
            const params = new URLSearchParams({ id: String(media.tmdbId) });
            if (media.type === 'tv') {
                params.set('season', String(media.s ?? 1));
                params.set('episode', String(media.e ?? 1));
            }

            const response = await fetch(
                `${this.BASE_URL}/wyzie?${params.toString()}`,
                {
                    headers: {
                        ...this.HEADERS,
                        Referer: referer
                    }
                }
            );

            if (!response.ok) {
                return [];
            }

            const subtitles = (await response.json()) as WyzieSubtitle[];

            return subtitles
                .filter((subtitle) => !!subtitle.url)
                .map((subtitle) => ({
                    url: this.createProxyUrl(subtitle.url!, {
                        ...this.HEADERS,
                        Referer: referer
                    }),
                    label: subtitle.display ?? subtitle.language ?? 'Subtitle',
                    format: this.inferSubtitleFormat(subtitle.url!)
                }));
        } catch {
            return [];
        }
    }

    private inferSubtitleFormat(url: string): SubtitleFormat {
        const path = (new URL(url).pathname.split('.').pop() ?? '').toLowerCase();
        if (path === 'srt') return 'srt';
        if (path === 'ass') return 'ass';
        if (path === 'ssa') return 'ssa';
        if (path === 'ttml') return 'ttml';
        return 'srt';
    }

    async healthCheck(): Promise<boolean> {
        try {
            const response = await fetch(this.BASE_URL, {
                method: 'HEAD',
                headers: this.HEADERS
            });
            return response.status < 500;
        } catch {
            return false;
        }
    }
}
