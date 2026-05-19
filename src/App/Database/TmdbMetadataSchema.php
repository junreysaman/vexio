<?php

declare(strict_types=1);

namespace App\Database;

use App\Support\MediaUrl;
use Framework\Database;

class TmdbMetadataSchema
{
    private static bool $ensured = false;

    public static function ensure(Database $db): void
    {
        if (self::$ensured) {
            return;
        }

        // Backfill slugs for any items that were inserted without one.
        self::backfillMediaItemSlugs($db);

        // Safety-net: create auxiliary tables if they were somehow dropped.
        foreach (self::tables() as $sql) {
            $db->query($sql);
        }

        self::$ensured = true;
    }

    private static function backfillMediaItemSlugs(Database $db): void
    {
        if (!$db->tableExists('media_items')) {
            return;
        }

        $items = $db->select(
            "SELECT id, title FROM media_items WHERE slug IS NULL OR slug = ''"
        );

        foreach ($items as $item) {
            $db->updateById('media_items', (int) $item['id'], [
                'slug' => MediaUrl::slugify((string) ($item['title'] ?? 'Untitled')),
            ]);
        }
    }

    private static function tables(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS content_meta (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                owner_type ENUM('item','season','episode') NOT NULL,
                owner_id   BIGINT UNSIGNED NOT NULL,
                meta_key   VARCHAR(120) NOT NULL,
                meta_value TEXT DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_content_meta_owner_key (owner_type, owner_id, meta_key),
                INDEX idx_content_meta_lookup (owner_type, owner_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS content_terms (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                taxonomy   VARCHAR(80) NOT NULL,
                name       VARCHAR(190) NOT NULL,
                slug       VARCHAR(220) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_content_terms_tax_slug (taxonomy, slug),
                INDEX idx_content_terms_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS content_term_links (
                owner_type ENUM('item','season','episode') NOT NULL,
                owner_id   BIGINT UNSIGNED NOT NULL,
                term_id    BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (owner_type, owner_id, term_id),
                INDEX idx_content_term_links_owner (owner_type, owner_id),
                CONSTRAINT fk_content_term_links_term
                    FOREIGN KEY (term_id) REFERENCES content_terms(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];
    }
}
