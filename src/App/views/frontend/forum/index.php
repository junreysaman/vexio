<?= $this->start('content') ?>
<div id="forum-page">

  <!-- HERO -->
  <div id="forum-hero">
    <div class="forum-hero-inner">
      <div class="forum-hero-top">
        <div>
          <div class="arch-breadcrumb">
            <a href="/">Home</a><span>›</span><span>Forum</span>
          </div>
          <div class="forum-hero-title">Community<br><span class="hl">Forum</span></div>
          <p class="forum-hero-sub">Discuss episodes, share reviews, ask for recommendations — all in one place.</p>
        </div>
        <a href="#" class="forum-new-btn" onclick="event.preventDefault();<?= $currentUser ? 'openNewThread()' : 'showToast(\'Sign in to post a thread\')' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Thread
        </a>
      </div>

      <div class="forum-stats-bar">
        <?php $stats = $stats ?? ['total'=>0,'members'=>0,'today'=>0,'counts'=>[]]; ?>
        <div class="fstat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><strong><?= number_format((int)$stats['total']) ?></strong> threads</div>
        <div class="fstat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><strong><?= number_format((int)$stats['members']) ?></strong> members</div>
        <div class="fstat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><strong><?= number_format((int)$stats['today']) ?></strong> posts today</div>
      </div>

      <div class="forum-filter-row">
        <?php
          $activeCategory = $activeCategory ?? '';
          $activeSort     = $activeSort ?? 'latest';
          $cats           = $categories ?? \App\Services\Forum\ForumService::CATEGORIES;
          $sorts          = $sortOptions ?? \App\Services\Forum\ForumService::SORT_OPTIONS;
        ?>
        <a href="/forum" class="fc-pill <?= $activeCategory === '' ? 'active' : '' ?>">All</a>
        <?php foreach ($cats as $key => $cat): ?>
          <a href="/forum?category=<?= escape($key) ?>&sort=<?= escape($activeSort) ?>"
             class="fc-pill <?= $activeCategory === $key ? 'active' : '' ?>">
            <?= escape($cat['label']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- MAIN -->
  <section id="forum-main">
    <div class="forum-shell">
      <div class="forum-layout">

        <!-- SIDEBAR -->
        <aside class="forum-sidebar">

          <div class="fsb-panel">
            <div class="fsb-head">Categories</div>
            <div class="fsb-list">
              <?php
                $catIcons = [
                  'discussion' => ['bg'=>'rgba(139,92,246,.15)','stroke'=>'#c4b5fd','path'=>'<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
                  'review'     => ['bg'=>'rgba(255,195,64,.1)', 'stroke'=>'var(--gold)','path'=>'<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>'],
                  'recommend'  => ['bg'=>'rgba(0,200,240,.1)',  'stroke'=>'var(--cyan)','path'=>'<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>'],
                  'news'       => ['bg'=>'rgba(232,23,63,.1)',  'stroke'=>'var(--accent2)','path'=>'<path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2z"/>'],
                  'help'       => ['bg'=>'rgba(34,197,94,.1)',  'stroke'=>'#4ade80','path'=>'<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
                  'offtopic'   => ['bg'=>'rgba(255,255,255,.05)','stroke'=>'var(--muted2)','path'=>'<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
                ];
                $catCounts = $stats['counts'] ?? [];
              ?>
              <a href="/forum" class="fsb-item <?= $activeCategory === '' ? 'active' : '' ?>">
                <div class="fsb-icon" style="background:rgba(139,92,246,.15);"><svg viewBox="0 0 24 24" fill="none" stroke="#c4b5fd" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
                <div class="fsb-info"><div class="fsb-name">All Threads</div><div class="fsb-count"><?= number_format((int)($stats['total'] ?? 0)) ?> threads</div></div>
              </a>
              <?php foreach ($cats as $key => $cat):
                $icon = $catIcons[$key] ?? $catIcons['offtopic'];
                $count = (int) ($catCounts[$key] ?? 0);
              ?>
              <a href="/forum?category=<?= escape($key) ?>" class="fsb-item <?= $activeCategory === $key ? 'active' : '' ?>">
                <div class="fsb-icon" style="background:<?= $icon['bg'] ?>;"><svg viewBox="0 0 24 24" fill="none" stroke="<?= $icon['stroke'] ?>" stroke-width="2"><?= $icon['path'] ?></svg></div>
                <div class="fsb-info"><div class="fsb-name"><?= escape($cat['label']) ?></div><div class="fsb-count"><?= number_format($count) ?> threads</div></div>
              </a>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="fsb-panel">
            <div class="fsb-head"><div class="online-dot"></div> Online Now</div>
            <div class="online-row">
              <div class="online-avatar" style="background:#e8173f;">K</div>
              <div class="online-avatar" style="background:#8b5cf6;">I</div>
              <div class="online-avatar" style="background:#00c8f0;">V</div>
              <div class="online-avatar" style="background:#ffc340;color:#000;">M</div>
              <div class="online-avatar" style="background:#22c55e;">A</div>
              <span class="online-more">+33 more</span>
            </div>
          </div>

        </aside>

        <!-- THREAD LIST -->
        <div>
          <div class="threads-toolbar">
            <?php $pageMeta = $meta ?? ['total'=>0,'current_page'=>1,'last_page'=>1]; ?>
            <div class="threads-count"><strong><?= number_format((int)$pageMeta['total']) ?></strong> threads</div>
            <div class="threads-sort">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
              <select onchange="window.location='/forum?category=<?= escape($activeCategory) ?>&sort='+this.value">
                <?php foreach ($sorts as $key => $label): ?>
                  <option value="<?= escape($key) ?>" <?= $activeSort === $key ? 'selected' : '' ?>><?= escape($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="forum-threads">
            <?php
            $threads = $threads ?? [];
            $catClass = ['discussion'=>'cat-discussion','review'=>'cat-review','recommend'=>'cat-recommend','news'=>'cat-news','help'=>'cat-help','offtopic'=>'cat-help'];
            ?>
            <?php if ($threads === []): ?>
              <div style="padding:48px 24px;text-align:center;background:var(--bg2);border:1px solid var(--border);border-radius:14px;">
                <div style="font-size:32px;margin-bottom:12px;">💬</div>
                <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px;">No threads yet</div>
                <div style="font-size:13px;color:var(--muted2);">Be the first to start a discussion.</div>
              </div>
            <?php else: ?>
            <?php foreach ($threads as $t): ?>
            <a href="<?= escape($t['url']) ?>" class="thread-item <?= $t['is_pinned'] ? 'pinned' : '' ?>">
              <div class="thread-vote">
                <button class="vote-btn" onclick="event.stopPropagation();event.preventDefault();voteThread(<?= (int)$t['id'] ?>,1,this)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg></button>
                <span class="vote-count" id="votes-<?= (int)$t['id'] ?>"><?= (int)$t['votes'] ?></span>
                <button class="vote-btn" onclick="event.stopPropagation();event.preventDefault();voteThread(<?= (int)$t['id'] ?>,-1,this)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></button>
              </div>
              <div class="thread-body">
                <div class="thread-tags">
                  <span class="thread-cat <?= $catClass[$t['category']] ?? 'cat-discussion' ?>"><?= escape($t['cat_label']) ?></span>
                  <?php if ($t['is_pinned']): ?><span class="thread-pin-badge">📌 PINNED</span><?php endif; ?>
                </div>
                <div class="thread-title"><?= escape($t['title']) ?></div>
                <div class="thread-preview"><?= escape($t['preview']) ?></div>
                <div class="thread-meta">
                  <div class="thread-avatar" style="background:var(--purple);"><?= strtoupper(substr($t['author'],0,1)) ?></div>
                  <span class="thread-author"><?= escape($t['author']) ?></span>
                  <span class="thread-sep">·</span>
                  <span class="thread-time"><?= escape($t['time_ago']) ?></span>
                </div>
              </div>
              <div class="thread-stats">
                <div class="ts-replies"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><?= number_format((int)$t['reply_count']) ?></div>
                <div class="ts-views"><?= number_format((int)$t['views']) ?> views</div>
                <?php if ($t['last_reply']): ?>
                  <div class="ts-last">by <?= escape($t['last_reply']) ?></div>
                <?php endif; ?>
              </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="forum-pagination">
            <?php
              $cp = (int)($pageMeta['current_page'] ?? 1);
              $lp = (int)($pageMeta['last_page'] ?? 1);
              $baseUrl = '/forum?category=' . urlencode($activeCategory) . '&sort=' . urlencode($activeSort) . '&page=';
            ?>
            <?php if ($cp > 1): ?>
              <a href="<?= escape($baseUrl . ($cp - 1)) ?>" class="fpg-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg></a>
            <?php else: ?>
              <button class="fpg-btn" disabled style="opacity:.4;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg></button>
            <?php endif; ?>
            <?php for ($p = max(1, $cp - 2); $p <= min($lp, $cp + 2); $p++): ?>
              <a href="<?= escape($baseUrl . $p) ?>" class="fpg-btn <?= $p === $cp ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($cp < $lp): ?>
              <a href="<?= escape($baseUrl . ($cp + 1)) ?>" class="fpg-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></a>
            <?php else: ?>
              <button class="fpg-btn" disabled style="opacity:.4;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></button>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </section>

</div><!-- /forum-page -->

<?php if ($currentUser): ?>
<!-- New Thread Modal — inside content section so layout wraps it -->
<div id="newThreadModal" class="forum-modal-overlay" onclick="if(event.target===this)closeNewThread()">
  <div class="forum-modal">
    <div class="forum-modal-head">
      <span>New Thread</span>
      <button onclick="closeNewThread()" class="forum-modal-close" aria-label="Close">✕</button>
    </div>
    <form id="newThreadForm">
      <input type="hidden" name="token" value="<?= escape($_SESSION['token'] ?? '') ?>">
      <div class="forum-field">
        <label>Category</label>
        <select name="category" required>
          <?php foreach ($categories ?? \App\Services\Forum\ForumService::CATEGORIES as $key => $cat): ?>
            <option value="<?= escape($key) ?>"><?= escape($cat['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="forum-field">
        <label>Title</label>
        <input type="text" name="title" id="threadTitle"
               placeholder="Thread title (5–200 characters)"
               required minlength="5" maxlength="200"
               oninput="document.getElementById('titleChars').textContent=this.value.length+'/200'">
        <span style="font-size:11px;color:var(--muted);text-align:right;" id="titleChars">0/200</span>
      </div>
      <div class="forum-field">
        <label>Body</label>
        <textarea name="body" id="threadBody" rows="7"
                  placeholder="Write your post here…"
                  required minlength="10" maxlength="10000"
                  oninput="document.getElementById('bodyChars').textContent=this.value.length+'/10000'"></textarea>
        <span style="font-size:11px;color:var(--muted);text-align:right;" id="bodyChars">0/10000</span>
      </div>
      <p id="threadFormError" hidden style="color:var(--accent2);font-size:13px;margin:0;"></p>
      <button type="submit" id="threadSubmitBtn" class="forum-new-btn" style="width:100%;justify-content:center;border-radius:8px;margin-top:4px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Post Thread
      </button>
    </form>
  </div>
</div>
<?php endif; ?>

<?= $this->end() ?>


<?= $this->start('scripts'); ?>
<script>
/* ── New Thread Modal ─────────────────────────────── */
const csrfToken = <?= json_encode($_SESSION['token'] ?? '') ?>;

function openNewThread() {
  document.getElementById('newThreadModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeNewThread() {
  document.getElementById('newThreadModal').classList.remove('open');
  document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('newThreadForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type=submit]');
    const err = document.getElementById('threadFormError');
    btn.disabled = true;
    btn.textContent = 'Posting…';
    err.hidden = true;

    try {
      const res = await fetch('/forum/threads', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
        body: new URLSearchParams(new FormData(form)),
      });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.error?.message || 'Failed to post.');
      window.location.href = data.thread.url;
    } catch (ex) {
      err.textContent = ex.message;
      err.hidden = false;
      btn.disabled = false;
      btn.textContent = 'Post Thread';
    }
  });
});

/* ── Voting ───────────────────────────────────────── */
async function voteThread(id, value, btn) {
  <?php if (!$currentUser): ?>
    showToast('Sign in to vote');
    return;
  <?php endif; ?>
  try {
    const res = await fetch('/forum/thread/' + id + '/vote', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
      body: new URLSearchParams({ token: csrfToken, value }),
    });
    const data = await res.json();
    if (data.ok) {
      document.getElementById('votes-' + id).textContent = data.votes;
    }
  } catch {}
}
</script>
<?= $this->end() ?>

<!-- New Thread Modal -->
<?php if ($currentUser): ?>
<div id="newThreadModal" class="forum-modal-overlay" onclick="if(event.target===this)closeNewThread()">
  <div class="forum-modal">
    <div class="forum-modal-head">
      <span>New Thread</span>
      <button onclick="closeNewThread()" class="forum-modal-close" aria-label="Close">✕</button>
    </div>
    <form id="newThreadForm">
      <input type="hidden" name="token" value="<?= escape($_SESSION['token'] ?? '') ?>">
      <div class="forum-field">
        <label>Category</label>
        <select name="category" required>
          <?php foreach ($categories ?? \App\Services\Forum\ForumService::CATEGORIES as $key => $cat): ?>
            <option value="<?= escape($key) ?>"><?= escape($cat['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="forum-field">
        <label>Title</label>
        <input type="text" name="title" id="threadTitle"
               placeholder="Thread title (5–200 characters)"
               required minlength="5" maxlength="200"
               oninput="document.getElementById('titleChars').textContent=this.value.length+'/200'">
        <span style="font-size:11px;color:var(--muted);text-align:right;" id="titleChars">0/200</span>
      </div>
      <div class="forum-field">
        <label>Body</label>
        <textarea name="body" id="threadBody" rows="7"
                  placeholder="Write your post here…"
                  required minlength="10" maxlength="10000"
                  oninput="document.getElementById('bodyChars').textContent=this.value.length+'/10000'"></textarea>
        <span style="font-size:11px;color:var(--muted);text-align:right;" id="bodyChars">0/10000</span>
      </div>
      <p id="threadFormError" hidden style="color:var(--accent2);font-size:13px;margin:0;"></p>
      <button type="submit" id="threadSubmitBtn" class="forum-new-btn" style="width:100%;justify-content:center;border-radius:8px;margin-top:4px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Post Thread
      </button>
    </form>
  </div>
</div>
<?php endif; ?>
