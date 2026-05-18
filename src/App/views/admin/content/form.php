<?= $this->start('content') ?>

<?php
$formData = array_merge($item ?? [], $oldFormData ?? []);
$contentId = (int) ($item['id'] ?? 0);
$type = (string) ($formData['type'] ?? 'movie');
$status = (string) ($formData['status'] ?? 'draft');
$poster = ($formData['poster_image'] ?? '') ?: (($formData['poster_url'] ?? '') ?: null);
$backdrop = ($formData['backdrop_image'] ?? '') ?: null;
$canImportSeasons = $type === 'tv_show' && !empty($formData['tmdb_id']);
$seasons = $hierarchy['seasons'] ?? [];
$episodes = $hierarchy['episodes'] ?? [];
?>

<div class="admin-page-head content-head">
    <div>
        <span class="paper-pill">Edit</span>
        <h1>Edit Content</h1>
        <p>Adjust imported catalogue metadata, publishing status, artwork paths, and episode details.</p>
    </div>
    <a class="btn btn-outline-secondary" href="/admin/content?type=<?= escape($type) ?>">
        <i class="icon-arrow_back mr-2"></i>Back to Content
    </a>
</div>

<form action="/admin/content/<?= $contentId ?>/edit" method="POST" class="content-form">
    <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">

    <div class="row my-3">
        <div class="col-lg-8">
            <div class="card no-b shadow-sm admin-card">
                <div class="card-body">
                    <h5 class="card-title">Metadata</h5>
                    <div class="form-group">
                        <label class="col-form-label s-12" for="title">TITLE</label>
                        <input class="form-control r-0 light s-12" id="title" name="title" type="text" value="<?= escape($formData['title'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label s-12" for="slug">SLUG</label>
                        <input class="form-control r-0 light s-12" id="slug" name="slug" type="text" value="<?= escape($formData['slug'] ?? '') ?>" placeholder="title-url-slug">
                    </div>

                    <div class="form-group">
                        <label class="col-form-label s-12" for="synopsis">SYNOPSIS</label>
                        <textarea class="form-control r-0 light s-12" id="synopsis" name="synopsis" rows="7"><?= escape($formData['synopsis'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="release_year">YEAR</label>
                            <input class="form-control r-0 light s-12" id="release_year" name="release_year" type="number" min="1900" max="2100" value="<?= escape((string) ($formData['release_year'] ?? '')) ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="stream_link">STREAM LINK</label>
                            <input class="form-control r-0 light s-12" id="stream_link" name="stream_link" type="url" value="<?= escape($formData['stream_link'] ?? '') ?>" placeholder="https://licensed-stream.example/title">
                        </div>
                    </div>
                </div>

                <hr>

                <div class="card-body">
                    <h5 class="card-title">Artwork</h5>
                    <div class="form-group">
                        <label class="col-form-label s-12" for="poster_url">REMOTE POSTER URL</label>
                        <input class="form-control r-0 light s-12" id="poster_url" name="poster_url" type="url" value="<?= escape($formData['poster_url'] ?? '') ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="poster_image">LOCAL POSTER IMAGE</label>
                            <input class="form-control r-0 light s-12" id="poster_image" name="poster_image" type="text" value="<?= escape($formData['poster_image'] ?? '') ?>" placeholder="/uploads/tmdb/movies/poster-id.webp">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="col-form-label s-12" for="backdrop_image">LOCAL BACKDROP IMAGE</label>
                            <input class="form-control r-0 light s-12" id="backdrop_image" name="backdrop_image" type="text" value="<?= escape($formData['backdrop_image'] ?? '') ?>" placeholder="/uploads/tmdb/movies/backdrop-id.webp">
                        </div>
                    </div>
                </div>

                <hr>

                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="icon-save mr-2"></i>Save Changes
                    </button>
                    <a href="/admin/content?type=<?= escape($type) ?>" class="btn btn-link">Cancel</a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card no-b shadow-sm admin-card content-preview-card">
                <?php if ($backdrop): ?>
                    <img class="content-backdrop-preview" src="<?= escape($backdrop) ?>" alt="">
                <?php endif; ?>
                <div class="card-body">
                    <div class="content-edit-preview">
                        <span class="content-thumb is-large">
                            <?php if ($poster): ?>
                                <img src="<?= escape($poster) ?>" alt="">
                            <?php else: ?>
                                <i class="icon-image"></i>
                            <?php endif; ?>
                        </span>
                        <div>
                            <strong><?= escape($formData['title'] ?? 'Untitled') ?></strong>
                            <small><?= escape((string) ($formData['tmdb_id'] ? 'TMDB #' . $formData['tmdb_id'] : 'Manual content')) ?></small>
                            <?php if (!empty($formData['watchUrl'])): ?>
                                <a class="btn btn-light btn-sm mt-2" href="<?= escape((string) $formData['watchUrl']) ?>">
                                    <i class="icon-play_arrow mr-1"></i>Watch Page
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card no-b shadow-sm admin-card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Publishing</h5>
                    <div class="form-group">
                        <label class="col-form-label s-12" for="type">TYPE</label>
                        <select class="custom-select form-control r-0 light s-12" id="type" name="type" required>
                            <?php foreach (($types ?? []) as $value => $label): ?>
                                <option value="<?= escape($value) ?>" <?= $type === $value ? 'selected' : '' ?>>
                                    <?= escape($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="col-form-label s-12" for="status">STATUS</label>
                        <select class="custom-select form-control r-0 light s-12" id="status" name="status" required>
                            <?php foreach (($statuses ?? []) as $value => $label): ?>
                                <option value="<?= escape($value) ?>" <?= $status === $value ? 'selected' : '' ?>>
                                    <?= escape($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="col-form-label s-12" for="views">VIEWS</label>
                        <input class="form-control r-0 light s-12" id="views" name="views" type="number" min="0" value="<?= escape((string) ($formData['views'] ?? 0)) ?>">
                    </div>

                    <div class="custom-control custom-checkbox mt-3">
                        <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" <?= !empty($formData['is_featured']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="is_featured">Feature in homepage hero</label>
                    </div>
                </div>
            </div>

            <div class="card no-b shadow-sm admin-card mt-3">
                <div class="card-body">
                    <h5 class="card-title">TMDB Ranking</h5>
                    <div class="form-group">
                        <label class="col-form-label s-12" for="tmdb_rating">RATING</label>
                        <input class="form-control r-0 light s-12" id="tmdb_rating" name="tmdb_rating" type="number" min="0" max="10" step="0.1" value="<?= escape((string) ($formData['tmdb_rating'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="col-form-label s-12" for="tmdb_popularity">POPULARITY</label>
                        <input class="form-control r-0 light s-12" id="tmdb_popularity" name="tmdb_popularity" type="number" min="0" step="0.001" value="<?= escape((string) ($formData['tmdb_popularity'] ?? '')) ?>">
                    </div>
                    <div class="form-group mb-0">
                        <label class="col-form-label s-12" for="tmdb_vote_count">VOTE COUNT</label>
                        <input class="form-control r-0 light s-12" id="tmdb_vote_count" name="tmdb_vote_count" type="number" min="0" value="<?= escape((string) ($formData['tmdb_vote_count'] ?? 0)) ?>">
                    </div>
                </div>
            </div>

            <?php if ($type === 'tv_show'): ?>
                <div class="card no-b shadow-sm admin-card mt-3">
                    <div class="card-body">
                        <h5 class="card-title">Generated Library</h5>
                        <div class="content-hierarchy-summary">
                            <span><strong><?= number_format(count($seasons)) ?></strong> Seasons</span>
                            <span><strong><?= number_format(count($episodes)) ?></strong> Episodes</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($type === 'tv_show'): ?>
    <section class="card no-b shadow-sm admin-card hierarchy-manager" id="seasons">
        <div class="card-body">
            <div class="hierarchy-head">
                <div>
                    <h5 class="card-title mb-1">Season Management</h5>
                    <p class="text-muted mb-0">Import seasons first, then import episodes from each season row.</p>
                </div>
                <div class="hierarchy-head-actions">
                    <?php if ($canImportSeasons): ?>
                        <button class="btn btn-primary btn-sm" type="submit" form="season-generator-form" onclick="return confirm('Import seasons for this TV show from TMDB? Episodes will not be imported yet.');">
                            <i class="icon-playlist_add mr-1"></i>Import Seasons
                        </button>
                    <?php endif; ?>
                    <span class="paper-pill"><?= number_format(count($seasons)) ?> seasons</span>
                </div>
            </div>

            <?php if (empty($seasons)): ?>
                <div class="empty-state">
                    <strong>No seasons yet</strong>
                    <span>Use the TMDB generator to create season records for this title.</span>
                </div>
            <?php endif; ?>

            <?php foreach ($seasons as $season): ?>
                <?php
                $seasonId = (int) $season['id'];
                $seasonPoster = ($season['poster_image'] ?? '') ?: (($season['poster_url'] ?? '') ?: null);
                ?>
                <form class="hierarchy-edit-card" action="/admin/content/<?= $contentId ?>/seasons/<?= $seasonId ?>/edit" method="POST">
                    <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
                    <div class="hierarchy-media">
                        <span class="content-thumb">
                            <?php if ($seasonPoster): ?>
                                <img src="<?= escape($seasonPoster) ?>" alt="">
                            <?php else: ?>
                                <i class="icon-image"></i>
                            <?php endif; ?>
                        </span>
                        <div>
                            <strong>Season <?= number_format((int) ($season['season_number'] ?? 0)) ?></strong>
                            <small><?= $season['tmdb_id'] ? 'TMDB #' . (int) $season['tmdb_id'] : 'Manual season' ?></small>
                        </div>
                    </div>
                    <div class="hierarchy-fields">
                        <input class="form-control r-0 light s-12" name="title" value="<?= escape($season['title'] ?? '') ?>" placeholder="Season title" required>
                        <input class="form-control r-0 light s-12" name="season_number" type="number" min="1" value="<?= escape((string) ($season['season_number'] ?? 1)) ?>" aria-label="Season number">
                        <input class="form-control r-0 light s-12" name="release_year" type="number" min="1900" max="2100" value="<?= escape((string) ($season['release_year'] ?? '')) ?>" placeholder="Year">
                        <select class="custom-select form-control r-0 light s-12" name="status" aria-label="Season status">
                            <?php foreach (($statuses ?? []) as $value => $label): ?>
                                <option value="<?= escape($value) ?>" <?= ($season['status'] ?? '') === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input class="form-control r-0 light s-12" name="poster_url" value="<?= escape($season['poster_url'] ?? '') ?>" placeholder="Remote poster URL">
                        <input class="form-control r-0 light s-12" name="poster_image" value="<?= escape($season['poster_image'] ?? '') ?>" placeholder="Local poster path">
                        <input class="form-control r-0 light s-12" name="backdrop_image" value="<?= escape($season['backdrop_image'] ?? '') ?>" placeholder="Local backdrop path">
                        <textarea class="form-control r-0 light s-12" name="synopsis" rows="2" placeholder="Synopsis"><?= escape($season['synopsis'] ?? '') ?></textarea>
                    </div>
                    <div class="hierarchy-actions">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="icon-save mr-1"></i>Save</button>
                        <?php if (!empty($formData['tmdb_id'])): ?>
                            <button class="btn btn-light btn-sm" type="submit" form="import-episodes-<?= $seasonId ?>" onclick="return confirm('Import episodes for this season from TMDB?');">
                                <i class="icon-playlist_add mr-1"></i>Import Episodes
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-outline-danger btn-sm" type="submit" form="delete-season-<?= $seasonId ?>" onclick="return confirm('Delete this season? Episodes are kept but detached from the season row.');">
                            <i class="icon-trash mr-1"></i>Delete
                        </button>
                    </div>
                </form>
                <?php if (!empty($formData['tmdb_id'])): ?>
                    <form id="import-episodes-<?= $seasonId ?>" action="/admin/content/<?= $contentId ?>/seasons/<?= $seasonId ?>/generate-episodes" method="POST">
                        <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
                        <input type="hidden" name="status" value="<?= escape((string) ($season['status'] ?? $status)) ?>">
                    </form>
                <?php endif; ?>
                <form id="delete-season-<?= $seasonId ?>" action="/admin/content/<?= $contentId ?>/seasons/<?= $seasonId ?>/delete" method="POST">
                    <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
                </form>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($canImportSeasons): ?>
        <form id="season-generator-form" action="/admin/content/<?= $contentId ?>/generate-seasons" method="POST">
            <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
            <input type="hidden" name="status" value="<?= escape($status) ?>">
        </form>
    <?php endif; ?>

    <section class="card no-b shadow-sm admin-card hierarchy-manager mt-3" id="episodes">
        <div class="card-body">
            <div class="hierarchy-head">
                <div>
                    <h5 class="card-title mb-1">Episode Management</h5>
                    <p class="text-muted mb-0">Manage episode order, stream links, artwork, views, and status.</p>
                </div>
                <span class="paper-pill"><?= number_format(count($episodes)) ?> episodes</span>
            </div>

            <form class="hierarchy-edit-card is-episode is-builder" action="/admin/content/<?= $contentId ?>/episodes" method="POST">
                <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
                <div class="hierarchy-media">
                    <span class="content-thumb">
                        <i class="icon-add"></i>
                    </span>
                    <div>
                        <strong>Add Episode</strong>
                        <small>Manual entry</small>
                    </div>
                </div>
                <div class="hierarchy-fields">
                    <input class="form-control r-0 light s-12" name="title" placeholder="Episode title" required>
                    <input class="form-control r-0 light s-12" name="season_number" type="number" min="1" value="<?= escape((string) (($seasons[0]['season_number'] ?? 1))) ?>" aria-label="Season number">
                    <input class="form-control r-0 light s-12" name="episode_number" type="number" min="1" value="1" aria-label="Episode number">
                    <input class="form-control r-0 light s-12" name="release_year" type="number" min="1900" max="2100" value="<?= escape((string) ($formData['release_year'] ?? '')) ?>" placeholder="Year">
                    <input class="form-control r-0 light s-12" name="views" type="number" min="0" value="0" placeholder="Views">
                    <select class="custom-select form-control r-0 light s-12" name="status" aria-label="Episode status">
                        <?php foreach (($statuses ?? []) as $value => $label): ?>
                            <option value="<?= escape($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input class="form-control r-0 light s-12" name="episode_name" placeholder="Display episode name">
                    <input class="form-control r-0 light s-12" name="stream_link" placeholder="Stream link">
                    <input class="form-control r-0 light s-12" name="poster_url" placeholder="Remote poster URL">
                    <input class="form-control r-0 light s-12" name="poster_image" placeholder="Local poster path">
                    <input class="form-control r-0 light s-12" name="backdrop_image" placeholder="Local backdrop path">
                    <textarea class="form-control r-0 light s-12" name="synopsis" rows="2" placeholder="Synopsis"></textarea>
                </div>
                <div class="hierarchy-actions">
                    <button class="btn btn-primary btn-sm" type="submit"><i class="icon-add mr-1"></i>Add</button>
                </div>
            </form>

            <?php if (empty($episodes)): ?>
                <div class="empty-state">
                    <strong>No episodes yet</strong>
                    <span>Use the TMDB generator to create episode records for this title.</span>
                </div>
            <?php endif; ?>

            <?php foreach ($episodes as $episode): ?>
                <?php
                $episodeId = (int) $episode['id'];
                $episodePoster = ($episode['poster_image'] ?? '') ?: (($episode['poster_url'] ?? '') ?: null);
                ?>
                <form class="hierarchy-edit-card is-episode" action="/admin/content/<?= $contentId ?>/episodes/<?= $episodeId ?>/edit" method="POST">
                    <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
                    <div class="hierarchy-media">
                        <span class="content-thumb">
                            <?php if ($episodePoster): ?>
                                <img src="<?= escape($episodePoster) ?>" alt="">
                            <?php else: ?>
                                <i class="icon-image"></i>
                            <?php endif; ?>
                        </span>
                        <div>
                            <strong>S<?= (int) ($episode['season_number'] ?? 0) ?> E<?= (int) ($episode['episode_number'] ?? 0) ?></strong>
                            <small><?= $episode['tmdb_id'] ? 'TMDB #' . (int) $episode['tmdb_id'] : 'Manual episode' ?></small>
                        </div>
                    </div>
                    <div class="hierarchy-fields">
                        <input class="form-control r-0 light s-12" name="title" value="<?= escape($episode['title'] ?? '') ?>" placeholder="Episode title" required>
                        <input class="form-control r-0 light s-12" name="season_number" type="number" min="1" value="<?= escape((string) ($episode['season_number'] ?? 1)) ?>" aria-label="Season number">
                        <input class="form-control r-0 light s-12" name="episode_number" type="number" min="1" value="<?= escape((string) ($episode['episode_number'] ?? 1)) ?>" aria-label="Episode number">
                        <input class="form-control r-0 light s-12" name="release_year" type="number" min="1900" max="2100" value="<?= escape((string) ($episode['release_year'] ?? '')) ?>" placeholder="Year">
                        <input class="form-control r-0 light s-12" name="views" type="number" min="0" value="<?= escape((string) ($episode['views'] ?? 0)) ?>" placeholder="Views">
                        <select class="custom-select form-control r-0 light s-12" name="status" aria-label="Episode status">
                            <?php foreach (($statuses ?? []) as $value => $label): ?>
                                <option value="<?= escape($value) ?>" <?= ($episode['status'] ?? '') === $value ? 'selected' : '' ?>><?= escape($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input class="form-control r-0 light s-12" name="stream_link" value="<?= escape($episode['stream_link'] ?? '') ?>" placeholder="Stream link">
                        <input class="form-control r-0 light s-12" name="poster_url" value="<?= escape($episode['poster_url'] ?? '') ?>" placeholder="Remote poster URL">
                        <input class="form-control r-0 light s-12" name="poster_image" value="<?= escape($episode['poster_image'] ?? '') ?>" placeholder="Local poster path">
                        <input class="form-control r-0 light s-12" name="backdrop_image" value="<?= escape($episode['backdrop_image'] ?? '') ?>" placeholder="Local backdrop path">
                        <textarea class="form-control r-0 light s-12" name="synopsis" rows="2" placeholder="Synopsis"><?= escape($episode['synopsis'] ?? '') ?></textarea>
                    </div>
                    <div class="hierarchy-actions">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="icon-save mr-1"></i>Save</button>
                        <button class="btn btn-outline-danger btn-sm" type="submit" form="delete-episode-<?= $episodeId ?>" onclick="return confirm('Delete this episode?');">
                            <i class="icon-trash mr-1"></i>Delete
                        </button>
                    </div>
                </form>
                <form id="delete-episode-<?= $episodeId ?>" action="/admin/content/<?= $contentId ?>/episodes/<?= $episodeId ?>/delete" method="POST">
                    <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
                </form>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?= $this->end() ?>
