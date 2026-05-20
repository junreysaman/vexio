<?= $this->start('content') ?>

<?php
$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$displayPath = $requestedPath === '/' ? 'Home' : $requestedPath;
?>

<main class="not-found-page">
  <section class="not-found-hero">
    <div class="not-found-bg" aria-hidden="true">
      <div class="nf-frame nf-frame-a"></div>
      <div class="nf-frame nf-frame-b"></div>
      <div class="nf-frame nf-frame-c"></div>
    </div>

    <div class="not-found-inner">
      <div class="nf-copy">
        <div class="nf-kicker">
          <span>404</span>
          <strong>Signal Lost</strong>
        </div>

        <h1><?= escape($title ?? 'Page Not Found') ?></h1>
        <p>
          <?= escape($message ?? 'The page you requested could not be found.') ?>
          The title may have moved, expired, or never made it into the archive.
        </p>

        <div class="nf-actions">
          <a class="nf-btn primary" href="/">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Home
          </a>
          <a class="nf-btn" href="/archive/browse">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
            Browse
          </a>
          <button class="nf-btn" type="button" onclick="document.getElementById('mobileSearchOpen')?.click() || document.getElementById('searchOpen')?.click()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Search
          </button>
        </div>
      </div>

      <aside class="nf-panel" aria-label="Missing page details">
        <div class="nf-code">404</div>
        <div class="nf-panel-copy">
          <span>Requested path</span>
          <strong><?= escape($displayPath) ?></strong>
        </div>
        <div class="nf-scan">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
        </div>
      </aside>
    </div>
  </section>
</main>

<style>
body.paper-not-found-page{
  background:var(--bg);
}
.not-found-page{
  min-height:100vh;
  background:var(--bg);
  overflow:hidden;
}
.not-found-hero{
  position:relative;
  min-height:calc(100vh - var(--nav-h));
  margin-top:var(--nav-h);
  display:flex;
  align-items:center;
  padding:70px 48px 92px;
  border-bottom:1px solid var(--border);
}
.not-found-hero::before{
  content:"";
  position:absolute;
  inset:0;
  background:
    linear-gradient(90deg,rgba(6,9,16,.98),rgba(6,9,16,.82) 52%,rgba(6,9,16,.94)),
    url('/uploads/tmdb/movies/backdrop-1669050.webp') center/cover no-repeat;
  opacity:.88;
}
.not-found-hero::after{
  content:"";
  position:absolute;
  inset:0;
  background:linear-gradient(0deg,var(--bg) 0%,transparent 42%,rgba(6,9,16,.78) 100%);
  pointer-events:none;
}
.not-found-bg{
  position:absolute;
  inset:0;
  z-index:1;
  pointer-events:none;
}
.nf-frame{
  position:absolute;
  border:1px solid rgba(255,255,255,.09);
  background:rgba(12,16,24,.58);
  box-shadow:0 26px 80px rgba(0,0,0,.38);
}
.nf-frame-a{right:8%;top:15%;width:190px;aspect-ratio:2/3;transform:rotate(8deg);}
.nf-frame-b{right:20%;bottom:13%;width:150px;aspect-ratio:2/3;transform:rotate(-10deg);}
.nf-frame-c{right:3%;bottom:25%;width:110px;aspect-ratio:2/3;transform:rotate(14deg);}
.not-found-inner{
  position:relative;
  z-index:2;
  width:min(1180px,100%);
  margin:0 auto;
  display:grid;
  grid-template-columns:minmax(0,1fr) 320px;
  gap:56px;
  align-items:center;
}
.nf-copy{
  max-width:720px;
}
.nf-kicker{
  display:inline-flex;
  align-items:center;
  gap:10px;
  min-height:34px;
  padding:0 13px;
  border:1px solid rgba(255,255,255,.13);
  border-radius:8px;
  background:rgba(12,16,24,.72);
  color:var(--cyan);
  font-size:11px;
  font-weight:900;
  letter-spacing:1.2px;
  text-transform:uppercase;
}
.nf-kicker span{
  color:#fff;
  background:var(--accent);
  border-radius:4px;
  padding:2px 7px;
}
.nf-copy h1{
  margin-top:18px;
  max-width:650px;
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(58px,10vw,128px);
  line-height:.86;
  letter-spacing:2px;
  color:#fff;
  text-shadow:0 18px 46px rgba(0,0,0,.45);
}
.nf-copy p{
  margin-top:18px;
  max-width:560px;
  color:var(--muted2);
  font-size:15px;
  line-height:1.8;
}
.nf-actions{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  margin-top:28px;
}
.nf-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:9px;
  min-height:44px;
  padding:0 18px;
  border-radius:8px;
  border:1px solid rgba(255,255,255,.12);
  background:rgba(18,24,36,.82);
  color:var(--text);
  font-size:13px;
  font-weight:900;
  transition:transform .15s,border-color .2s,background .2s;
}
.nf-btn:hover{
  transform:translateY(-1px);
  border-color:var(--cyan);
  background:rgba(30,42,58,.9);
}
.nf-btn.primary{
  border-color:transparent;
  background:var(--accent);
  color:#fff;
  box-shadow:0 16px 38px rgba(232,23,63,.32);
}
.nf-btn.primary:hover{
  border-color:transparent;
  background:var(--accent2);
}
.nf-btn svg{
  width:16px;
  height:16px;
  flex-shrink:0;
}
.nf-panel{
  border:1px solid rgba(255,255,255,.13);
  border-radius:8px;
  background:rgba(12,16,24,.78);
  backdrop-filter:blur(18px);
  box-shadow:0 28px 90px rgba(0,0,0,.42);
  padding:24px;
  overflow:hidden;
}
.nf-code{
  font-family:'Bebas Neue',sans-serif;
  font-size:104px;
  line-height:.85;
  letter-spacing:3px;
  color:#fff;
  text-shadow:0 0 32px rgba(232,23,63,.32);
}
.nf-panel-copy{
  display:grid;
  gap:5px;
  margin-top:16px;
  padding-top:18px;
  border-top:1px solid rgba(255,255,255,.09);
}
.nf-panel-copy span{
  color:var(--muted);
  font-size:11px;
  font-weight:900;
  letter-spacing:1px;
  text-transform:uppercase;
}
.nf-panel-copy strong{
  color:var(--text);
  font-size:13px;
  line-height:1.5;
  overflow-wrap:anywhere;
}
.nf-scan{
  display:grid;
  gap:7px;
  margin-top:22px;
}
.nf-scan span{
  display:block;
  height:9px;
  border-radius:99px;
  background:linear-gradient(90deg,rgba(232,23,63,.7),rgba(0,200,240,.25),rgba(255,255,255,.05));
}
.nf-scan span:nth-child(2){width:78%;}
.nf-scan span:nth-child(3){width:58%;}
.nf-scan span:nth-child(4){width:88%;}
@media(max-width:860px){
  .not-found-hero{
    padding:52px 18px 88px;
    align-items:flex-start;
  }
  .not-found-inner{
    grid-template-columns:1fr;
    gap:28px;
  }
  .nf-panel{
    max-width:420px;
  }
  .nf-frame{
    opacity:.35;
  }
}
@media(max-width:520px){
  .nf-copy h1{
    font-size:64px;
  }
  .nf-copy p{
    font-size:14px;
  }
  .nf-actions{
    display:grid;
    grid-template-columns:1fr;
  }
}
</style>

<?= $this->end() ?>
