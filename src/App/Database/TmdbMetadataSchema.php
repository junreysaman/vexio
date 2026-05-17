<?php

declare(strict_types=1);

namespace App\Database;

use Framework\Database;

class TmdbMetadataSchema
{
    public static function ensure(Database $db): void
    {
        foreach (self::mediaItemColumns() as $column => $definition) {
            self::addColumnIfMissing($db, 'media_items', $column, $definition);
        }

        foreach (self::mediaSeasonColumns() as $column => $definition) {
            self::addColumnIfMissing($db, 'media_seasons', $column, $definition);
        }

        foreach (self::mediaEpisodeColumns() as $column => $definition) {
            self::addColumnIfMissing($db, 'media_episodes', $column, $definition);
        }

        foreach (self::tables() as $sql) {
            $db->query($sql);
        }
    }

    private static function addColumnIfMissing(Database $db, string $table, string $column, string $definition): void
    {
        $exists = $db->exists(
            'SELECT 1 FROM information_schema.columns
             WHERE table_schema = DATABASE()
             AND table_name = :table
             AND column_name = :column
             LIMIT 1',
            ['table' => $table, 'column' => $column]
        );

        if (!$exists) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }

    private static function mediaItemColumns(): array
    {
        return [
            'original_title' => 'VARCHAR(190) DEFAULT NULL AFTER `title`',
            'original_language' => 'VARCHAR(10) DEFAULT NULL AFTER `original_title`',
            'release_date' => 'DATE DEFAULT NULL AFTER `release_year`',
            'runtime_minutes' => 'SMALLINT UNSIGNED DEFAULT NULL AFTER `release_date`',
            'tmdb_status' => 'VARCHAR(60) DEFAULT NULL AFTER `runtime_minutes`',
            'tagline' => 'VARCHAR(255) DEFAULT NULL AFTER `tmdb_status`',
            'homepage_url' => 'VARCHAR(500) DEFAULT NULL AFTER `tagline`',
            'adult' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `homepage_url`',
            'budget' => 'BIGINT UNSIGNED DEFAULT NULL AFTER `adult`',
            'revenue' => 'BIGINT UNSIGNED DEFAULT NULL AFTER `budget`',
            'number_of_seasons' => 'SMALLINT UNSIGNED DEFAULT NULL AFTER `revenue`',
            'number_of_episodes' => 'SMALLINT UNSIGNED DEFAULT NULL AFTER `number_of_seasons`',
            'last_air_date' => 'DATE DEFAULT NULL AFTER `number_of_episodes`',
            'in_production' => 'TINYINT(1) DEFAULT NULL AFTER `last_air_date`',
            'origin_country' => 'VARCHAR(120) DEFAULT NULL AFTER `in_production`',
            'spoken_languages' => 'VARCHAR(255) DEFAULT NULL AFTER `origin_country`',
            'imdb_id' => 'VARCHAR(40) DEFAULT NULL AFTER `tmdb_id`',
            'youtube_id' => 'VARCHAR(255) DEFAULT NULL AFTER `stream_link`',
            'rated' => 'VARCHAR(40) DEFAULT NULL AFTER `youtube_id`',
            'country' => 'VARCHAR(120) DEFAULT NULL AFTER `rated`',
            'imagenes' => 'TEXT DEFAULT NULL AFTER `country`',
            'dt_cast' => 'TEXT DEFAULT NULL AFTER `imagenes`',
            'dt_dir' => 'TEXT DEFAULT NULL AFTER `dt_cast`',
            'dt_creator' => 'TEXT DEFAULT NULL AFTER `dt_dir`',
            'dt_featured_post' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_featured`',
            'clgnrt' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `dt_featured_post`',
        ];
    }

    private static function mediaSeasonColumns(): array
    {
        return [
            'serie' => 'VARCHAR(190) DEFAULT NULL AFTER `title`',
            'air_date' => 'DATE DEFAULT NULL AFTER `release_year`',
            'clgnrt' => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`',
        ];
    }

    private static function mediaEpisodeColumns(): array
    {
        return [
            'serie' => 'VARCHAR(190) DEFAULT NULL AFTER `title`',
            'episode_name' => 'VARCHAR(190) DEFAULT NULL AFTER `serie`',
            'air_date' => 'DATE DEFAULT NULL AFTER `release_year`',
            'imagenes' => 'TEXT DEFAULT NULL AFTER `air_date`',
        ];
    }

    private static function tables(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS content_meta (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                owner_type ENUM('item', 'season', 'episode') NOT NULL,
                owner_id BIGINT UNSIGNED NOT NULL,
                meta_key VARCHAR(120) NOT NULL,
                meta_value TEXT DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_content_meta_owner_key (owner_type, owner_id, meta_key),
                INDEX idx_content_meta_key (meta_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS content_terms (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                taxonomy VARCHAR(80) NOT NULL,
                name VARCHAR(190) NOT NULL,
                slug VARCHAR(220) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_content_terms_tax_slug (taxonomy, slug),
                INDEX idx_content_terms_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS content_term_links (
                owner_type ENUM('item', 'season', 'episode') NOT NULL,
                owner_id BIGINT UNSIGNED NOT NULL,
                term_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (owner_type, owner_id, term_id),
                CONSTRAINT fk_content_term_links_term
                    FOREIGN KEY (term_id) REFERENCES content_terms(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];
    }
}
