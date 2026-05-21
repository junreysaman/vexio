<?php use App\Support\MediaImage; ?>
<?php
  $scheduleItems = !empty($releaseSchedule) && is_array($releaseSchedule) ? $releaseSchedule : [];
  $todayTs = strtotime('today') ?: time();
  $activeDay = date('D', $todayTs);
  $days = [];
  $scheduleByDay = [];

  foreach ($scheduleItems as $item) {
      $itemDay = (string) ($item['day'] ?? '');
      if ($itemDay !== '') {
          $scheduleByDay[$itemDay][] = $item;
      }
  }

  for ($offset = 0; $offset < 7; $offset++) {
      $dayTs = strtotime('+' . $offset . ' day', $todayTs) ?: $todayTs;
      $key = date('D', $dayTs);
      $days[] = [
          'key' => $key,
          'label' => date('D', $dayTs),
          'date' => date('M j', $dayTs),
          'is_today' => $offset === 0,
          'count' => count($scheduleByDay[$key] ?? []),
      ];
  }
?>
<section id="schedule">
  <div class="container">
    <div class="sec-head">
      <h2 class="sec-title">
        <div class="sec-title-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        </div>
        Release <span class="accent">Schedule</span>
      </h2>
    </div>
    <div class="day-tabs" id="dayTabs" role="tablist" aria-label="Release schedule days">
      <?php foreach ($days as $day): ?>
        <button class="day-tab<?= $day['key'] === $activeDay ? ' active' : '' ?>" type="button" data-day="<?= escape($day['key']) ?>" role="tab" aria-selected="<?= $day['key'] === $activeDay ? 'true' : 'false' ?>">
          <span><?= escape($day['label']) ?><?= $day['is_today'] ? ' (Today)' : '' ?></span>
          <small><?= escape($day['date']) ?><?= $day['count'] > 0 ? ' - ' . (int) $day['count'] : '' ?></small>
        </button>
      <?php endforeach; ?>
    </div>
    <div class="sched-slider-shell" id="schedGrid">
      <?php foreach ($days as $dayInfo): ?>
        <?php
          $dayKey = (string) $dayInfo['key'];
          $dayItems = $scheduleByDay[$dayKey] ?? [];
          $rowId = 'schedule-row-' . preg_replace('/[^A-Za-z0-9_-]/', '', $dayKey);
        ?>
        <div class="sched-day-panel" data-day-panel="<?= escape($dayKey) ?>"<?= $dayKey !== $activeDay ? ' hidden' : '' ?>>
          <?php if ($dayItems !== []): ?>
            <div class="hrow-wrap sched-wrap">
              <button class="hrow-btn prev" type="button" onclick="scrollRow('<?= escape($rowId) ?>',-1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></button>
              <div class="hrow sched-row" id="<?= escape($rowId) ?>">
                <?php foreach ($dayItems as $item): ?>
                  <?php
                    $day = (string) ($item['day'] ?? '');
                    $showTitle = (string) ($item['show_title'] ?? 'TV Show');
                    $episodeTitle = (string) ($item['title'] ?? '');
                    $label = (string) ($item['label'] ?? '');
                    $dateLabel = (string) ($item['date_label'] ?? 'TBA');
                    $timeLabel = (string) ($item['time_label'] ?? 'Release day');
                    $watchUrl = (string) (($item['watchUrl'] ?? '') ?: '#');
                    $posterMedia = is_array($item['poster_media'] ?? null) ? $item['poster_media'] : MediaImage::posterFromRow($item, 'schedulePoster');
                  ?>
                  <a class="sched-card" data-day="<?= escape($day) ?>" href="<?= escape($watchUrl) ?>">
                    <div class="sched-thumb">
                      <?php if (MediaImage::srcOnly($posterMedia) !== ''): ?>
                        <?php echo $this->includePartial('/frontend/partials/media-image', [
                            'media' => $posterMedia,
                            'alt' => $showTitle . ' poster',
                            'loading' => 'lazy',
                        ]); ?>
                      <?php else: ?>
                        <div class="sched-ph" aria-hidden="true">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div class="sched-info">
                      <div class="sched-time"><?= escape($dateLabel) ?> - <?= escape($timeLabel) ?></div>
                      <div class="sched-title"><?= escape($showTitle) ?></div>
                      <div class="sched-ep">
                        <span><?= escape($label !== '' ? $label : $episodeTitle) ?></span>
                        <span class="sched-new">SOON</span>
                      </div>
                      <?php if ($episodeTitle !== '' && $episodeTitle !== $label): ?>
                        <div class="sched-name"><?= escape($episodeTitle) ?></div>
                      <?php endif; ?>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
              <button class="hrow-btn next" type="button" onclick="scrollRow('<?= escape($rowId) ?>',1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></button>
            </div>
          <?php else: ?>
            <div class="hrow-wrap sched-wrap">
              <div class="hrow sched-row">
                <div class="sched-empty">
                  <strong>No releases for <?= escape((string) $dayInfo['label']) ?></strong>
                  <span>Check another day in the 7-day schedule.</span>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
