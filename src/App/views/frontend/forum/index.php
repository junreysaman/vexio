<?= $this->start('styles') ?>
<style>
/* ═══════════════════════════════════════════════════════
   FORUM PAGE
═══════════════════════════════════════════════════════ */
#forum-page{background:var(--bg);min-height:100vh;padding-bottom:80px;}

/* ── Hero ── */
#forum-hero{
  margin-top:var(--nav-h);
  background:var(--bg2);
  border-bottom:1px solid var(--border);
  padding:40px 0 0;
  position:relative;overflow:hidden;
}
#forum-hero::before{
  content:'';position:absolute;inset:0;pointer-events:none;
  background:
    radial-gradient(ellipse 55% 140% at 85% 50%,rgba(139,92,246,.09) 0%,transparent 65%),
    radial-gradient(ellipse 40% 90% at 8% 80%,rgba(232,23,63,.07) 0%,transparent 60%),
    radial-gradient(ellipse 30% 60% at 50% 0%,rgba(0,200,240,.05) 0%,transparent 60%);
}
#forum-hero::after{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg,transparent,var(--purple),var(--accent),var(--cyan),transparent);
  opacity:.7;
}
.forum-hero-inner{position:relative;z-index:1;max-width:1440px;margin:0 auto;padding:0 48px;}
@media(max-width:768px){.forum-hero-inner{padding:0 16px;}}

.forum-hero-top{
  display:flex;align-items:flex-end;justify-content:space-between;
  gap:24px;flex-wrap:wrap;margin-bottom:6px;
}
.forum-hero-title{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(42px,6vw,72px);
  letter-spacing:2px;line-height:.95;
}
.forum-hero-title .hl{
  background:linear-gradient(135deg,var(--purple),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
}
.forum-hero-sub{
  font-size:13px;color:var(--muted2);font-weight:500;
  margin-top:10px;line-height:1.5;max-width:480px;
}
.forum-new-btn{
  display:flex;align-items:center;gap:8px;
  padding:11px 24px;border-radius:99px;
  font-size:13px;font-weight:700;
  background:var(--purple);color:#fff;
  transition:background .2s,transform .15s;
  box-shadow:0 0 20px rgba(139,92,246,.35);
  flex-shrink:0;align-self:flex-start;margin-top:8px;
}
.forum-new-btn:hover{background:#7c3aed;transform:translateY(-1px);}
.forum-new-btn svg{width:14px;height:14px;}

/* Stats bar */
.forum-stats-bar{
  display:flex;align-items:center;gap:24px;
  padding:18px 0 0;flex-wrap:wrap;
}
.fstat{
  display:flex;align-items:center;gap:8px;
  font-size:12px;font-weight:700;color:var(--muted2);
}
.fstat svg{width:14px;height:14px;color:var(--purple);}
.fstat strong{color:var(--text);}

/* Category filter pills */
.forum-filter-row{
  display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;
  padding:16px 0 22px;
}
.forum-filter-row::-webkit-scrollbar{display:none;}
.fc-pill{
  flex-shrink:0;padding:7px 16px;border-radius:99px;
  font-size:12px;font-weight:700;letter-spacing:.3px;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted2);cursor:pointer;transition:all .2s;white-space:nowrap;
}
.fc-pill:hover{border-color:var(--purple);color:var(--purple);}
.fc-pill.active{background:rgba(139,92,246,.15);border-color:var(--purple);color:#c4b5fd;}

/* ── Main layout ── */
#forum-main{padding:36px 0 80px;}
.forum-shell{max-width:1440px;margin:0 auto;padding:0 48px;}
@media(max-width:768px){.forum-shell{padding:0 16px;}}
.forum-layout{display:grid;grid-template-columns:260px 1fr;gap:28px;align-items:start;}
@media(max-width:900px){.forum-layout{grid-template-columns:1fr;}}

/* ── Sidebar ── */
.forum-sidebar{position:sticky;top:calc(var(--nav-h) + 16px);display:flex;flex-direction:column;gap:12px;}
@media(max-width:900px){.forum-sidebar{position:static;}}

.fsb-panel{background:var(--bg2);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
.fsb-head{
  padding:14px 18px;
  font-size:11px;font-weight:800;letter-spacing:1px;
  text-transform:uppercase;color:var(--muted);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:8px;
}
.fsb-list{display:flex;flex-direction:column;}
.fsb-item{
  display:flex;align-items:center;gap:12px;
  padding:12px 18px;cursor:pointer;
  border-bottom:1px solid var(--border);
  transition:background .2s;text-decoration:none;color:inherit;
}
.fsb-item:last-child{border-bottom:none;}
.fsb-item:hover{background:rgba(255,255,255,.03);}
.fsb-item.active{background:rgba(139,92,246,.08);}
.fsb-icon{
  width:32px;height:32px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.fsb-icon svg{width:14px;height:14px;}
.fsb-info{flex:1;min-width:0;}
.fsb-name{font-size:13px;font-weight:700;color:var(--text);}
.fsb-count{font-size:11px;color:var(--muted);font-weight:600;}
.fsb-badge{
  font-size:10px;font-weight:800;padding:2px 7px;border-radius:99px;
  background:rgba(139,92,246,.15);color:#c4b5fd;
  border:1px solid rgba(139,92,246,.25);flex-shrink:0;
}

/* Online users */
.online-row{display:flex;align-items:center;padding:14px 18px;gap:0;}
.online-avatar{
  width:28px;height:28px;border-radius:50%;
  border:2px solid var(--bg2);
  display:flex;align-items:center;justify-content:center;
  font-size:10px;font-weight:800;color:#fff;
  margin-left:-6px;flex-shrink:0;
}
.online-avatar:first-child{margin-left:0;}
.online-more{margin-left:10px;font-size:11px;font-weight:700;color:var(--muted2);}
.online-dot{
  width:7px;height:7px;border-radius:50%;
  background:#22c55e;box-shadow:0 0 6px #22c55e;
  animation:pulse-green 1.6s infinite;
}
@keyframes pulse-green{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.5;transform:scale(1.3);}}

/* ── Threads toolbar ── */
.threads-toolbar{
  display:flex;align-items:center;gap:12px;
  margin-bottom:16px;flex-wrap:wrap;
}
.threads-count{font-size:13px;color:var(--muted2);font-weight:600;flex:1;}
.threads-count strong{color:var(--text);}
.threads-sort{
  display:flex;align-items:center;gap:8px;
  background:var(--bg3);border:1px solid var(--border);
  border-radius:8px;padding:8px 12px;
}
.threads-sort svg{width:13px;height:13px;color:var(--muted);}
.threads-sort select{
  background:none;border:none;outline:none;
  font-family:inherit;font-size:12px;font-weight:700;
  color:var(--text);cursor:pointer;-webkit-appearance:none;
}
.threads-sort select option{background:var(--bg3);}

/* ── Thread list ── */
.forum-threads{display:flex;flex-direction:column;}
.thread-item{
  display:grid;grid-template-columns:40px 1fr auto;
  gap:16px;align-items:start;
  padding:18px 20px;
  background:var(--bg2);
  border:1px solid var(--border);
  border-bottom:none;
  cursor:pointer;
  transition:background .2s,border-color .2s;
  text-decoration:none;color:inherit;
}
.thread-item:first-child{border-radius:14px 14px 0 0;}
.thread-item:last-child{border-bottom:1px solid var(--border);border-radius:0 0 14px 14px;}
.thread-item:only-child{border-radius:14px;border-bottom:1px solid var(--border);}
.thread-item:hover{background:rgba(255,255,255,.025);}
.thread-item.pinned{border-left:3px solid var(--gold);}
.thread-item.hot{border-left:3px solid var(--accent);}

/* Vote column */
.thread-vote{
  display:flex;flex-direction:column;align-items:center;gap:3px;padding-top:2px;
}
.vote-btn{
  width:26px;height:26px;border-radius:6px;
  display:flex;align-items:center;justify-content:center;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted);transition:all .2s;
}
.vote-btn:hover{border-color:var(--purple);color:var(--purple);}
.vote-btn svg{width:11px;height:11px;}
.vote-count{font-size:12px;font-weight:800;color:var(--text);}

/* Thread body */
.thread-body{min-width:0;}
.thread-tags{display:flex;align-items:center;gap:6px;margin-bottom:7px;flex-wrap:wrap;}
.thread-cat{
  font-size:10px;font-weight:800;letter-spacing:.5px;
  padding:2px 8px;border-radius:4px;text-transform:uppercase;
}
.cat-discussion{background:rgba(139,92,246,.15);color:#c4b5fd;border:1px solid rgba(139,92,246,.25);}
.cat-review{background:rgba(255,195,64,.12);color:var(--gold);border:1px solid rgba(255,195,64,.25);}
.cat-recommend{background:rgba(0,200,240,.12);color:var(--cyan);border:1px solid rgba(0,200,240,.25);}
.cat-news{background:rgba(232,23,63,.12);color:var(--accent2);border:1px solid rgba(232,23,63,.25);}
.cat-help{background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.25);}
.thread-pin-badge{font-size:10px;font-weight:800;padding:2px 8px;border-radius:4px;background:rgba(255,195,64,.12);color:var(--gold);border:1px solid rgba(255,195,64,.25);}
.thread-hot-badge{font-size:10px;font-weight:800;padding:2px 8px;border-radius:4px;background:rgba(232,23,63,.12);color:var(--accent2);border:1px solid rgba(232,23,63,.25);}

.thread-title{font-size:15px;font-weight:700;line-height:1.35;color:var(--text);margin-bottom:6px;}
.thread-item:hover .thread-title{color:#c4b5fd;}
.thread-preview{
  font-size:12px;color:var(--muted2);line-height:1.5;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
  margin-bottom:10px;
}
.thread-meta{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.thread-avatar{
  width:22px;height:22px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:10px;font-weight:800;color:#fff;flex-shrink:0;
}
.thread-author{font-size:12px;font-weight:700;color:var(--muted2);}
.thread-time{font-size:11px;color:var(--muted);}
.thread-sep{color:var(--dim);}

/* Thread stats */
.thread-stats{
  display:flex;flex-direction:column;align-items:flex-end;gap:5px;
  flex-shrink:0;min-width:70px;
}
.ts-replies{display:flex;align-items:center;gap:4px;font-size:12px;font-weight:700;color:var(--muted2);}
.ts-replies svg{width:12px;height:12px;}
.ts-views{font-size:11px;color:var(--muted);}
.ts-last{font-size:10px;color:var(--muted);text-align:right;line-height:1.4;}

/* Pagination */
.forum-pagination{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:24px;}
.fpg-btn{
  width:36px;height:36px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  font-size:13px;font-weight:700;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted2);cursor:pointer;transition:all .2s;
}
.fpg-btn:hover{border-color:var(--purple);color:var(--purple);}
.fpg-btn.active{background:var(--purple);border-color:var(--purple);color:#fff;}
.fpg-btn svg{width:13px;height:13px;}
.fpg-dots{color:var(--muted);padding:0 4px;font-size:13px;}

@media(max-width:600px){
  .thread-item{grid-template-columns:1fr auto;}
  .thread-vote{display:none;}
}
</style>
<?= $this->end() ?>

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

</div>
<?= $this->end() ?>

<?= $this->start('scripts') ?>
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
      <button onclick="closeNewThread()" class="forum-modal-close">✕</button>
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
        <input type="text" name="title" placeholder="Thread title (5–200 characters)" required minlength="5" maxlength="200">
      </div>
      <div class="forum-field">
        <label>Body</label>
        <textarea name="body" rows="6" placeholder="Write your post here…" required minlength="10" maxlength="10000"></textarea>
      </div>
      <p id="threadFormError" hidden style="color:var(--accent2);font-size:13px;margin-bottom:8px;"></p>
      <button type="submit" class="forum-new-btn" style="width:100%;justify-content:center;border-radius:8px;">Post Thread</button>
    </form>
  </div>
</div>

<style>
.forum-modal-overlay{
  position:fixed;inset:0;z-index:600;
  background:rgba(6,9,16,.85);backdrop-filter:blur(8px);
  display:flex;align-items:center;justify-content:center;
  opacity:0;pointer-events:none;transition:opacity .25s;
  padding:16px;
}
.forum-modal-overlay.open{opacity:1;pointer-events:all;}
.forum-modal{
  background:var(--bg2);border:1px solid var(--border2);
  border-radius:16px;width:100%;max-width:560px;
  padding:28px;display:flex;flex-direction:column;gap:20px;
  max-height:90vh;overflow-y:auto;
}
.forum-modal-head{
  display:flex;align-items:center;justify-content:space-between;
  font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:1.5px;
}
.forum-modal-close{
  width:32px;height:32px;border-radius:50%;
  background:var(--bg3);border:1px solid var(--border);
  color:var(--muted);font-size:14px;
  display:flex;align-items:center;justify-content:center;
  transition:all .2s;
}
.forum-modal-close:hover{background:var(--accent);color:#fff;border-color:var(--accent);}
.forum-field{display:flex;flex-direction:column;gap:6px;}
.forum-field label{font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);}
.forum-field input,.forum-field select,.forum-field textarea{
  background:var(--bg3);border:1px solid var(--border);
  border-radius:8px;padding:10px 14px;
  font-family:inherit;font-size:13px;color:var(--text);
  outline:none;transition:border-color .2s;resize:vertical;
}
.forum-field input:focus,.forum-field select:focus,.forum-field textarea:focus{border-color:var(--purple);}
.forum-field select option{background:var(--bg3);}
</style>
<?php endif; ?>
