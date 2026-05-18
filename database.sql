-- Vexio database rebuilt around DooPlay DBMVS importer content.
-- Default login row:
--   username: admin
--   email: admin@example.com
--   password: password

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS content_term_links;
DROP TABLE IF EXISTS content_terms;
DROP TABLE IF EXISTS content_meta;
DROP TABLE IF EXISTS media_comments;
DROP TABLE IF EXISTS media_episodes;
DROP TABLE IF EXISTS media_seasons;
DROP TABLE IF EXISTS media_items;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_users_roles FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(190) NOT NULL,
    slug VARCHAR(220) DEFAULT NULL,
    original_title VARCHAR(190) DEFAULT NULL,
    original_language VARCHAR(10) DEFAULT NULL,
    type ENUM('movie', 'tv_show') NOT NULL,
    synopsis TEXT DEFAULT NULL,
    poster_url VARCHAR(500) DEFAULT NULL,
    poster_image VARCHAR(500) DEFAULT NULL,
    backdrop_image VARCHAR(500) DEFAULT NULL,
    stream_link VARCHAR(500) DEFAULT NULL,
    youtube_id VARCHAR(255) DEFAULT NULL,
    rated VARCHAR(40) DEFAULT NULL,
    country VARCHAR(120) DEFAULT NULL,
    imagenes TEXT DEFAULT NULL,
    dt_cast TEXT DEFAULT NULL,
    dt_dir TEXT DEFAULT NULL,
    dt_creator TEXT DEFAULT NULL,
    tmdb_id BIGINT UNSIGNED DEFAULT NULL,
    imdb_id VARCHAR(40) DEFAULT NULL,
    tmdb_type ENUM('movie', 'tv_show') DEFAULT NULL,
    release_year SMALLINT UNSIGNED DEFAULT NULL,
    release_date DATE DEFAULT NULL,
    runtime_minutes SMALLINT UNSIGNED DEFAULT NULL,
    tmdb_status VARCHAR(60) DEFAULT NULL,
    tagline VARCHAR(255) DEFAULT NULL,
    homepage_url VARCHAR(500) DEFAULT NULL,
    adult TINYINT(1) NOT NULL DEFAULT 0,
    budget BIGINT UNSIGNED DEFAULT NULL,
    revenue BIGINT UNSIGNED DEFAULT NULL,
    number_of_seasons SMALLINT UNSIGNED DEFAULT NULL,
    number_of_episodes SMALLINT UNSIGNED DEFAULT NULL,
    last_air_date DATE DEFAULT NULL,
    in_production TINYINT(1) DEFAULT NULL,
    origin_country VARCHAR(120) DEFAULT NULL,
    spoken_languages VARCHAR(255) DEFAULT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    dt_featured_post TINYINT(1) NOT NULL DEFAULT 0,
    clgnrt TINYINT(1) NOT NULL DEFAULT 0,
    tmdb_rating DECIMAL(3,1) DEFAULT NULL,
    tmdb_popularity DECIMAL(10,3) DEFAULT NULL,
    tmdb_vote_count INT UNSIGNED NOT NULL DEFAULT 0,
    views BIGINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_media_items_tmdb (tmdb_type, tmdb_id),
    INDEX idx_media_items_slug (slug),
    INDEX idx_media_items_featured (status, is_featured),
    INDEX idx_media_items_status_views (status, views),
    INDEX idx_media_items_type (type),
    INDEX idx_media_items_imdb (imdb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media_seasons (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    media_item_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    serie VARCHAR(190) DEFAULT NULL,
    synopsis TEXT DEFAULT NULL,
    poster_url VARCHAR(500) DEFAULT NULL,
    poster_image VARCHAR(500) DEFAULT NULL,
    backdrop_image VARCHAR(500) DEFAULT NULL,
    tmdb_id BIGINT UNSIGNED DEFAULT NULL,
    tmdb_parent_id BIGINT UNSIGNED DEFAULT NULL,
    tmdb_type ENUM('tv_season') DEFAULT NULL,
    season_number SMALLINT UNSIGNED NOT NULL,
    release_year SMALLINT UNSIGNED DEFAULT NULL,
    air_date DATE DEFAULT NULL,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    clgnrt TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_media_seasons_parent_number (media_item_id, season_number),
    UNIQUE KEY uq_media_seasons_tmdb (tmdb_type, tmdb_id),
    INDEX idx_media_seasons_parent (media_item_id),
    CONSTRAINT fk_media_seasons_media_item
        FOREIGN KEY (media_item_id) REFERENCES media_items(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media_episodes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    media_item_id BIGINT UNSIGNED NOT NULL,
    media_season_id BIGINT UNSIGNED DEFAULT NULL,
    title VARCHAR(190) NOT NULL,
    serie VARCHAR(190) DEFAULT NULL,
    episode_name VARCHAR(190) DEFAULT NULL,
    synopsis TEXT DEFAULT NULL,
    poster_url VARCHAR(500) DEFAULT NULL,
    poster_image VARCHAR(500) DEFAULT NULL,
    backdrop_image VARCHAR(500) DEFAULT NULL,
    stream_link VARCHAR(500) DEFAULT NULL,
    tmdb_id BIGINT UNSIGNED DEFAULT NULL,
    tmdb_parent_id BIGINT UNSIGNED DEFAULT NULL,
    tmdb_type ENUM('tv_episode') DEFAULT NULL,
    season_number SMALLINT UNSIGNED NOT NULL,
    episode_number SMALLINT UNSIGNED NOT NULL,
    release_year SMALLINT UNSIGNED DEFAULT NULL,
    air_date DATE DEFAULT NULL,
    imagenes TEXT DEFAULT NULL,
    views BIGINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_media_episodes_parent_number (media_item_id, season_number, episode_number),
    UNIQUE KEY uq_media_episodes_tmdb (tmdb_type, tmdb_id),
    INDEX idx_media_episodes_parent (media_item_id),
    INDEX idx_media_episodes_season (media_season_id),
    INDEX idx_media_episodes_status_views (status, views),
    CONSTRAINT fk_media_episodes_media_item
        FOREIGN KEY (media_item_id) REFERENCES media_items(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_media_episodes_media_season
        FOREIGN KEY (media_season_id) REFERENCES media_seasons(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE content_meta (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE content_terms (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    taxonomy VARCHAR(80) NOT NULL,
    name VARCHAR(190) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_content_terms_tax_slug (taxonomy, slug),
    INDEX idx_content_terms_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE content_term_links (
    owner_type ENUM('item', 'season', 'episode') NOT NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (owner_type, owner_id, term_id),
    CONSTRAINT fk_content_term_links_term
        FOREIGN KEY (term_id) REFERENCES content_terms(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media_comments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    owner_type ENUM('item', 'episode') NOT NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    display_name VARCHAR(140) NOT NULL,
    body TEXT NOT NULL,
    likes INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('published', 'hidden') NOT NULL DEFAULT 'published',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_media_comments_owner (owner_type, owner_id, status, created_at),
    INDEX idx_media_comments_parent (parent_id),
    INDEX idx_media_comments_user (user_id),
    CONSTRAINT fk_media_comments_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, name, description) VALUES
    (1, 'superuser', 'Full administrative access'),
    (2, 'regular', 'Standard user account');

INSERT INTO users (
    id,
    first_name,
    last_name,
    username,
    email,
    password,
    role_id,
    is_active
) VALUES (
    1,
    'Vexio',
    'Admin',
    'admin',
    'admin@example.com',
    '$2y$12$Pgzlscldkr.eqUufUJoosObyaKPnpogiEnywfzMR7ObOiMKbFo4da',
    1,
    1
);

SET FOREIGN_KEY_CHECKS = 1;
