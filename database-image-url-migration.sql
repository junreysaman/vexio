-- Migrate Vexio artwork storage from local image columns to remote URL columns.
-- Run this once against the old schema, before deploying code that expects backdrop_url.

SET NAMES utf8mb4;

ALTER TABLE media_items
    ADD COLUMN backdrop_url VARCHAR(500) DEFAULT NULL AFTER poster_url;

ALTER TABLE media_seasons
    ADD COLUMN backdrop_url VARCHAR(500) DEFAULT NULL AFTER poster_url;

ALTER TABLE media_episodes
    ADD COLUMN backdrop_url VARCHAR(500) DEFAULT NULL AFTER poster_url;

UPDATE media_items
SET
    poster_url = COALESCE(NULLIF(poster_url, ''), NULLIF(CASE WHEN poster_image LIKE 'http%' THEN poster_image ELSE NULL END, '')),
    backdrop_url = COALESCE(NULLIF(backdrop_url, ''), NULLIF(CASE WHEN backdrop_image LIKE '/uploads/%' THEN NULL ELSE backdrop_image END, ''))
WHERE
    (poster_url IS NULL OR poster_url = '' OR backdrop_url IS NULL OR backdrop_url = '')
    AND (
        poster_image IS NOT NULL OR backdrop_image IS NOT NULL
    );

UPDATE media_seasons
SET
    poster_url = COALESCE(NULLIF(poster_url, ''), NULLIF(CASE WHEN poster_image LIKE 'http%' THEN poster_image ELSE NULL END, '')),
    backdrop_url = COALESCE(NULLIF(backdrop_url, ''), NULLIF(CASE WHEN backdrop_image LIKE '/uploads/%' THEN NULL ELSE backdrop_image END, ''))
WHERE
    (poster_url IS NULL OR poster_url = '' OR backdrop_url IS NULL OR backdrop_url = '')
    AND (
        poster_image IS NOT NULL OR backdrop_image IS NOT NULL
    );

UPDATE media_episodes
SET
    poster_url = COALESCE(NULLIF(poster_url, ''), NULLIF(CASE WHEN poster_image LIKE 'http%' THEN poster_image ELSE NULL END, '')),
    backdrop_url = COALESCE(NULLIF(backdrop_url, ''), NULLIF(CASE WHEN backdrop_image LIKE '/uploads/%' THEN NULL ELSE backdrop_image END, ''))
WHERE
    (poster_url IS NULL OR poster_url = '' OR backdrop_url IS NULL OR backdrop_url = '')
    AND (
        poster_image IS NOT NULL OR backdrop_image IS NOT NULL
    );

ALTER TABLE media_items
    DROP COLUMN poster_image,
    DROP COLUMN backdrop_image;

ALTER TABLE media_seasons
    DROP COLUMN poster_image,
    DROP COLUMN backdrop_image;

ALTER TABLE media_episodes
    DROP COLUMN poster_image,
    DROP COLUMN backdrop_image;

ALTER TABLE media_items
    DROP INDEX idx_media_items_featured,
    DROP INDEX idx_media_items_status_views,
    DROP INDEX idx_media_items_type,
    ADD INDEX idx_media_items_featured (status, is_featured, updated_at),
    ADD INDEX idx_media_items_status_views (status, views, updated_at),
    ADD INDEX idx_media_items_type_status (type, status, release_year),
    ADD INDEX idx_media_items_popular (status, tmdb_rating, views);

ALTER TABLE media_seasons
    ADD INDEX idx_media_seasons_status (status, air_date);

ALTER TABLE media_episodes
    DROP INDEX idx_media_episodes_status_views,
    ADD INDEX idx_media_episodes_status_views (status, views, updated_at),
    ADD INDEX idx_media_episodes_airing (status, air_date);
