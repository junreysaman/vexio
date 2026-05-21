<?php

declare(strict_types=1);

namespace App\Support;

class LocaleDisplay
{
    private const LANGUAGE_NAMES = [
        'ja' => 'Japanese',
        'en' => 'English',
        'ko' => 'Korean',
        'zh' => 'Chinese',
        'cn' => 'Chinese',
        'fr' => 'French',
        'de' => 'German',
        'es' => 'Spanish',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'th' => 'Thai',
        'hi' => 'Hindi',
        'id' => 'Indonesian',
        'ms' => 'Malay',
        'tl' => 'Tagalog',
        'vi' => 'Vietnamese',
    ];

    private const COUNTRY_NAMES = [
        'JP' => 'Japan',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'KR' => 'South Korea',
        'CN' => 'China',
        'HK' => 'Hong Kong',
        'TW' => 'Taiwan',
        'FR' => 'France',
        'DE' => 'Germany',
        'ES' => 'Spain',
        'IT' => 'Italy',
        'BR' => 'Brazil',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'MY' => 'Malaysia',
        'PH' => 'Philippines',
        'TH' => 'Thailand',
        'VN' => 'Vietnam',
        'CA' => 'Canada',
        'AU' => 'Australia',
    ];

    public static function languageName(?string $language): string
    {
        $code = strtolower(trim((string) $language));
        if ($code === '') {
            return 'N/A';
        }

        return self::LANGUAGE_NAMES[$code] ?? strtoupper($code);
    }

    public static function countryName(?string $country): string
    {
        $code = strtoupper(trim((string) $country));
        if ($code === '') {
            return 'N/A';
        }

        return self::COUNTRY_NAMES[$code] ?? self::titleFromToken($country);
    }

    /**
     * @return list<array{code: string, name: string, slug: string}>
     */
    public static function countryList(?string $value): array
    {
        $countries = [];

        foreach (preg_split('/[,|]+/', (string) $value) ?: [] as $raw) {
            $raw = trim($raw);
            if ($raw === '') {
                continue;
            }

            $code = strtoupper($raw);
            $name = self::countryName($raw);
            if ($name === 'N/A') {
                continue;
            }

            $slug = MediaUrl::slugify($name);
            $countries[$slug] = [
                'code' => preg_match('/^[A-Z]{2}$/', $code) ? $code : '',
                'name' => $name,
                'slug' => $slug,
            ];
        }

        return array_values($countries);
    }

    public static function countryNames(?string $value): string
    {
        $names = array_map(
            fn(array $country): string => $country['name'],
            self::countryList($value)
        );

        return $names === [] ? 'N/A' : implode(', ', $names);
    }

    private static function titleFromToken(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 'N/A';
        }

        return ucwords(strtolower(str_replace(['_', '-'], ' ', $value)));
    }
}

