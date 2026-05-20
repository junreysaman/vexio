<?= $this->start('styles') ?>
<style>
#forum-page{background:var(--bg);min-height:100vh;padding-bottom:80px;}

/* Hero */
#forum-hero{
  margin-top:var(--nav-h);background:var(--bg2);
  border-bottom:1px solid var(--border);padding:32px 0;
  position:relative;overflow:hidden;
}
#forum-hero::after{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,transparent,var(--purple),var(--accent),var(--cyan),transparent);
  opacity:.7;
}
.thread-hero-inner{position:relative;z-index:1;max-width:1440px;margin:0 auto;padding:0 48px;}
@media(max-width:768px){.thread-hero-inner{padding:0 16px;}}

/* Main */
#forum-main{padding:36px 0 80px;}
.forum-shell{max-width:1440px;margin:0 auto;padding:0 48px;}
@media(max-width:768px){.forum-shell{padding:0 16px;}}
.thread-layout{display:grid;grid-template-columns:1fr 300px;gap:28px;align-items:start;}
@media(max-width:900px){.thread-layout{grid-template-columns:1fr;}}

/* Thread post */
.thread-post{
  background:var(--bg2);border:1px solid var(--border);
  border-radius:14px;overflow:hidden;margin-bottom:24px;
}
.thread-post-head{
  display:flex;align-items:center;gap:12px;
  padding:16px 20px;border-bottom:1px solid var(--border);
}
.post-avatar{
  width:38px;height:38px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:14px;font-weight:800;color:#fff;flex-shrink:0;
  background:var(--purple);
}
.post-author-name{font-size:14px;font-weight:700;color:var(--text);}
.post-time{font-size:11px;color:var(--muted);}
.thread-post-body{
  padding:20px;font-size:14px;color:var(--muted2);
  line-height:1.8;white-space:pre-wrap;word-break:break-word;
}
.thread-post-foot{
  display:flex;align-items:center;gap:12px;
  padding:12px 20px;border-top:1px solid var(--border);
}
.vote-row{display:flex;align-items:center;gap:6px;}
.vote-btn{
  width:28px;height:28px;border-radius:6px;
  display:flex;align-items:center;justify-content:center;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted);transition:all .2s;
}
.vote-btn:hover{border-color:var(--purple);color:var(--purple);}
.vote-btn svg{width:12px;height:12px;}
.vote-count{font-size:13px;font-weight:800;color:var(--text);}

/* Replies */
.replies-head{
  display:flex;align-items:center;gap:14px;margin-bottom:16px;
}
.replies-title{
  font-family:'Bebas Neue',sans-serif;font-size:18px;letter-spacing:1.5px;color:var(--muted);
}
.replies-count{
  font-size:12px;font-weight:700;color:var(--muted2);
  background:var(--bg3);border:1px solid var(--border);
  padding:3px 10px;border-radius:99px;
}
.reply-item{
  background:var(--bg2);border:1px solid var(--border);
  border-radius:12px;margin-bottom:12px;overflow:hidden;
}
.reply-head{
  display:flex;align-items:center;gap:10px;
  padding:12px 16px;border-bottom:1px solid var(--border);
}
.reply-avatar{
  width:30px;height:30px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:800;color:#fff;flex-shrink:0;
  background:var(--accent);
}
.reply-author{font-size:13px;font-weight:700;color:var(--text);}
.reply-time{font-size:11px;color:var(--muted);}
.reply-body{
  padding:14px 16px;font-size:13px;color:var(--muted2);
  line-height:1.7;white-space:pre-wrap;word-break:break-word;
}

/* Reply form */
.reply-form-wrap{
  background:var(--bg2);border:1px solid var(--border);
  border-radius:14px;padding:20px;margin-top:24px;
}
.reply-form-title{
  font-size:13px;font-weight:800;letter-spacing:.5px;
  text-transform:uppercase;color:var(--muted);margin-bottom:14px;
}
.reply-form-wrap textarea{
  width:100%;background:var(--bg3);border:1px solid var(--border);
  border-radius:8px;padding:12px 14px;
  font-family:inherit;font-size:13px;color:var(--text);
  outline:none;resize:vertical;min-height:100px;
  transition:border-color .2s;
}
.reply-form-wrap textarea:focus{border-color:var(--purple);}
.reply-form-actions{
  display:flex;align-items:center;justify-content:space-between;
  margin-top:10px;flex-wrap:wrap;gap:8px;
}
.reply-submit-btn{
  display:flex;align-items:center;gap:8px;
  padding:10px 22px;border-radius:99px;
  font-size:13px;font-weight:700;
  background:var(--purple);color:#fff;
  transition:background .2s;
}
.reply-submit-btn:hover{background:#7c3aed;}
.reply-submit-btn:disabled{opacity:.6;cursor:not-allowed;}
.reply-char-count{font-size:11px;color:var(--muted);}
#replyError{color:var(--accent2);font-size:12px;}

/* Sidebar */
.thread-sidebar{display:flex;flex-direction:column;gap:14px;}
.tsb-panel{
  background:var(--bg2);border:1px solid var(--border);
  border-radius:14px;padding:18px;
}
.tsb-head{
  font-size:11px;font-weight:800;letter-spacing:1px;
  text-transform:uppercase;color:var(--muted);
  margin-bottom:14px;
}
.tsb-stat{
  display:flex;align-items:center;justify-content:space-between;
  padding:8px 0;border-bottom:1px solid var(--border);
  font-size:13px;
}
.tsb-stat:last-child{border-bottom:none;}
.tsb-stat-label{color:var(--muted2);font-weight:600;}
.tsb-stat-val{color:var(--text);font-weight:700;}

/* Pagination */
.reply-pagination{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:20px;}
.fpg-btn{
  width:36px;height:36px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  font-size:13px;font-weight:700;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted2);cursor:pointer;transition:all .2s;
  text-decoration:none;
}
.fpg-btn:hover{border-color:var(--purple);color:var(--purple);}
.fpg-btn.active{background:var(--purple);border-color:var(--purple);color:#fff;}
.fpg-btn svg{width:13px;height:13px;}

/* Cat badge */
.thread-cat{
  font-size:10px;font-weight:800;letter-spacing:.5px;
  padding:3px 10px;border-radius:4px;text-transform:uppercase;
}
.cat-discussion{background:rgba(139,92,246,.15);color:#c4b5fd;border:1px solid rgba(139,92,246,.25);}
.cat-review{background:rgba(255,195,64,.12);color:var(--gold);border:1px solid rgba(255,195,64,.25);}
.cat-recommend{background:rgba(0,200,240,.12);color:var(--cyan);border:1px solid rgba(0,200,240,.25);}
.cat-news{background:rgba(232,23,63,.12);color:var(--accent2);border:1px solid rgba(232,23,63,.25);}
.cat-help{background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.25);}
</style>
<?= $this->end() ?>

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
