<?= $this->start('content') ?>
<?php
$thread     = $thread ?? [];
$replies    = $replies ?? [];
$replyMeta  = $replyMeta ?? ['total'=>0,'current_page'=>1,'last_page'=>1];
$categories = $categories ?? \App\Services\Forum\ForumService::CATEGORIES;
$catClass   = ['discussion'=>'cat-discussion','review'=>'cat-review','recommend'=>'cat-recommend','news'=>'cat-news','help'=>'cat-help','offtopic'=>'cat-help'];
?>
<div id="forum-page">

  <!-- HERO -->
  <div id="forum-hero">
    <div class="thread-hero-inner">
      <div class="arch-breadcrumb" style="margin-bottom:12px;">
        <a href="/">Home</a><span>›</span>
        <a href="/forum">Forum</a><span>›</span>
        <?php if (!empty($thread['category'])): ?>
          <a href="/forum?category=<?= escape($thread['category']) ?>"><?= escape($thread['cat_label'] ?? $thread['category']) ?></a>
          <span>›</span>
        <?php endif; ?>
        <span><?= escape(mb_substr($thread['title'] ?? '', 0, 60)) ?><?= strlen($thread['title'] ?? '') > 60 ? '…' : '' ?></span>
      </div>
      <div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <span class="thread-cat <?= $catClass[$thread['category'] ?? 'discussion'] ?? 'cat-discussion' ?>">
          <?= escape($thread['cat_label'] ?? '') ?>
        </span>
        <?php if (!empty($thread['is_pinned'])): ?>
          <span style="font-size:10px;font-weight:800;padding:3px 8px;border-radius:4px;background:rgba(255,195,64,.12);color:var(--gold);border:1px solid rgba(255,195,64,.25);">📌 PINNED</span>
        <?php endif; ?>
      </div>
      <h1 style="font-family:'Bebas Neue',sans-serif;font-size:clamp(26px,4vw,44px);letter-spacing:1.5px;line-height:1.05;margin-top:10px;">
        <?= escape($thread['title'] ?? '') ?>
      </h1>
    </div>
  </div>

  <!-- MAIN -->
  <section id="forum-main">
    <div class="forum-shell">
      <div class="thread-layout">

        <!-- LEFT: post + replies + reply form -->
        <div>

          <!-- Original post -->
          <div class="thread-post">
            <div class="thread-post-head">
              <div class="post-avatar"><?= strtoupper(substr($thread['author'] ?? 'A', 0, 1)) ?></div>
              <div>
                <div class="post-author-name"><?= escape($thread['author'] ?? 'Anonymous') ?></div>
                <div class="post-time"><?= escape($thread['time_ago'] ?? '') ?> · <?= escape(date('M j, Y', strtotime($thread['created_at'] ?? 'now'))) ?></div>
              </div>
            </div>
            <div class="thread-post-body"><?= escape($thread['body'] ?? '') ?></div>
            <div class="thread-post-foot">
              <div class="vote-row">
                <button class="vote-btn" onclick="voteThread(<?= (int)($thread['id'] ?? 0) ?>,1)">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                </button>
                <span class="vote-count" id="thread-votes"><?= (int)($thread['votes'] ?? 0) ?></span>
                <button class="vote-btn" onclick="voteThread(<?= (int)($thread['id'] ?? 0) ?>,-1)">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
              </div>
              <span style="font-size:12px;color:var(--muted);"><?= number_format((int)($thread['views'] ?? 0)) ?> views</span>
              <button class="vote-btn" onclick="openShareModal('<?= escape(url($_SERVER['REQUEST_URI'] ?? '/')) ?>', '<?= escape($thread['title'] ?? 'Forum Thread') ?>', '/favicon.png')" style="margin-left:auto;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
              </button>
            </div>
          </div>

          <!-- Replies -->
          <?php if ($replies !== []): ?>
          <div class="replies-head">
            <div class="replies-title">Replies</div>
            <span class="replies-count"><?= number_format((int)$replyMeta['total']) ?></span>
          </div>

          <?php foreach ($replies as $reply): ?>
          <div class="reply-item">
            <div class="reply-head">
              <div class="reply-avatar"><?= strtoupper(substr($reply['author'] ?? 'A', 0, 1)) ?></div>
              <div>
                <div class="reply-author"><?= escape($reply['author'] ?? 'Anonymous') ?></div>
                <div class="reply-time"><?= escape($reply['time_ago'] ?? '') ?></div>
              </div>
            </div>
            <div class="reply-body"><?= escape($reply['body'] ?? '') ?></div>
          </div>
          <?php endforeach; ?>

          <?php if ($replyMeta['last_page'] > 1): ?>
          <div class="reply-pagination">
            <?php
              $cp = (int)$replyMeta['current_page'];
              $lp = (int)$replyMeta['last_page'];
              $tid = (int)($thread['id'] ?? 0);
              $base = '/forum/thread/' . $tid . '?page=';
            ?>
            <?php if ($cp > 1): ?>
              <a href="<?= escape($base . ($cp-1)) ?>" class="fpg-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg></a>
            <?php endif; ?>
            <?php for ($p = max(1,$cp-2); $p <= min($lp,$cp+2); $p++): ?>
              <a href="<?= escape($base . $p) ?>" class="fpg-btn <?= $p===$cp?'active':'' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($cp < $lp): ?>
              <a href="<?= escape($base . ($cp+1)) ?>" class="fpg-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <?php endif; ?>

          <!-- Reply form -->
          <div class="reply-form-wrap">
            <?php if ($currentUser): ?>
              <div class="reply-form-title">Post a Reply</div>
              <form id="replyForm">
                <input type="hidden" name="token" value="<?= escape($_SESSION['token'] ?? '') ?>">
                <textarea name="body" id="replyBody" placeholder="Write your reply…" maxlength="5000" oninput="document.getElementById('replyChars').textContent=this.value.length+'/5000'"></textarea>
                <div class="reply-form-actions">
                  <span class="reply-char-count" id="replyChars">0/5000</span>
                  <p id="replyError" hidden></p>
                  <button type="submit" class="reply-submit-btn" id="replyBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Post Reply
                  </button>
                </div>
              </form>
            <?php else: ?>
              <div style="text-align:center;padding:12px 0;">
                <p style="font-size:13px;color:var(--muted2);margin-bottom:12px;">Sign in to join the discussion.</p>
                <a href="/login" class="reply-submit-btn" style="display:inline-flex;">Sign In</a>
              </div>
            <?php endif; ?>
          </div>

        </div>

        <!-- SIDEBAR -->
        <aside class="thread-sidebar">
          <div class="tsb-panel">
            <div class="tsb-head">Thread Info</div>
            <div class="tsb-stat"><span class="tsb-stat-label">Author</span><span class="tsb-stat-val"><?= escape($thread['author'] ?? '—') ?></span></div>
            <div class="tsb-stat"><span class="tsb-stat-label">Category</span><span class="tsb-stat-val"><?= escape($thread['cat_label'] ?? '—') ?></span></div>
            <div class="tsb-stat"><span class="tsb-stat-label">Replies</span><span class="tsb-stat-val"><?= number_format((int)($thread['reply_count'] ?? 0)) ?></span></div>
            <div class="tsb-stat"><span class="tsb-stat-label">Views</span><span class="tsb-stat-val"><?= number_format((int)($thread['views'] ?? 0)) ?></span></div>
            <div class="tsb-stat"><span class="tsb-stat-label">Votes</span><span class="tsb-stat-val" id="sidebar-votes"><?= (int)($thread['votes'] ?? 0) ?></span></div>
            <div class="tsb-stat"><span class="tsb-stat-label">Posted</span><span class="tsb-stat-val"><?= escape(date('M j, Y', strtotime($thread['created_at'] ?? 'now'))) ?></span></div>
          </div>
          <a href="/forum" class="tsb-panel" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;transition:border-color .2s;" onmouseover="this.style.borderColor='var(--purple)'" onmouseout="this.style.borderColor='var(--border)'">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
            <span style="font-size:13px;font-weight:700;">Back to Forum</span>
          </a>
        </aside>

      </div>
    </div>
  </section>

</div>
<?= $this->end() ?>

<?= $this->start('scripts') ?>
<script>
const csrfToken = <?= json_encode($_SESSION['token'] ?? '') ?>;
const threadId  = <?= (int)($thread['id'] ?? 0) ?>;

/* ── Reply form ─────────────────────────────────── */
const replyForm = document.getElementById('replyForm');
if (replyForm) {
  replyForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('replyBtn');
    const err = document.getElementById('replyError');
    btn.disabled = true;
    btn.textContent = 'Posting…';
    err.hidden = true;

    try {
      const res = await fetch('/forum/thread/' + threadId + '/replies', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
        body: new URLSearchParams(new FormData(replyForm)),
      });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.error?.message || 'Failed to post reply.');

      // Refresh page to show new reply
      window.location.reload();
    } catch (ex) {
      err.textContent = ex.message;
      err.hidden = false;
      btn.disabled = false;
      btn.textContent = 'Post Reply';
    }
  });
}

/* ── Voting ─────────────────────────────────────── */
async function voteThread(id, value) {
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
      const v = data.votes;
      const el = document.getElementById('thread-votes');
      const sb = document.getElementById('sidebar-votes');
      if (el) el.textContent = v;
      if (sb) sb.textContent = v;
    }
  } catch {}
}
</script>
<?= $this->end() ?>
