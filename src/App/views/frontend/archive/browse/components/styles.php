<style>
/* ═══════════════════════════════════════════════════════
   RESET & DESIGN TOKENS
═══════════════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --bg:       #060910;
  --bg2:      #0c1018;
  --bg3:      #121824;
  --card:     #0f1520;
  --border:   rgba(255,255,255,.07);
  --border2:  rgba(255,255,255,.12);
  --accent:   #e8173f;
  --accent2:  #ff5e7d;
  --cyan:     #00c8f0;
  --gold:     #ffc340;
  --purple:   #8b5cf6;
  --text:     #f0f4ff;
  --muted:    #64748b;
  --muted2:   #8899b0;
  --dim:      #1e2a3a;
  --r:        10px;
  --nav-h:    68px;
  --bot-nav-h:64px;
}
html{scroll-behavior:smooth;}
body{
  background:var(--bg); color:var(--text);
  font-family:'Plus Jakarta Sans',sans-serif;
  font-size:14px; line-height:1.6;
  overflow-x:hidden;
  padding-bottom:var(--bot-nav-h);
}
@media(min-width:769px){body{padding-bottom:0;}}
a{text-decoration:none;color:inherit;}
img{display:block;max-width:100%;}
button{cursor:pointer;border:none;background:none;font-family:inherit;color:inherit;}
ul{list-style:none;}
::-webkit-scrollbar{width:4px;height:4px;}
::-webkit-scrollbar-track{background:var(--bg2);}
::-webkit-scrollbar-thumb{background:var(--dim);border-radius:99px;}

/* ═══════════════════════════════════════════════════════
   TOP NAVIGATION
═══════════════════════════════════════════════════════ */
#topnav{
  position:fixed;top:0;left:0;right:0;z-index:200;
  height:var(--nav-h);
  display:flex;align-items:center;
  padding:0 40px;
  background:rgba(6,9,16,.94);
  backdrop-filter:blur(20px);
  box-shadow:0 1px 0 var(--border);
}
.vx-logo{
  font-family:'Bebas Neue',sans-serif;
  font-size:32px;letter-spacing:3px;
  background:linear-gradient(135deg,var(--accent2) 0%,var(--accent) 50%,#c2001f 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  flex-shrink:0;margin-right:48px;
}
.vx-logo sup{
  font-family:'Plus Jakarta Sans',sans-serif;
  font-size:9px;font-weight:700;letter-spacing:1px;
  -webkit-text-fill-color:var(--cyan);
  background:none;-webkit-background-clip:unset;
  position:relative;top:-16px;left:2px;
}
.nav-links{display:flex;align-items:center;gap:4px;flex:1;}
.nav-link{
  display:flex;align-items:center;gap:8px;
  padding:8px 16px;border-radius:8px;
  font-size:13px;font-weight:600;
  color:var(--muted2);
  transition:color .2s,background .2s;
  white-space:nowrap;
}
.nav-link svg{width:16px;height:16px;flex-shrink:0;}
.nav-link:hover{color:var(--text);background:rgba(255,255,255,.05);}
.nav-link.active{color:var(--text);background:rgba(232,23,63,.1);}
.nav-link.active svg{color:var(--accent);}
.nav-right{display:flex;align-items:center;gap:10px;margin-left:auto;}
.nav-search-bar{
  display:flex;align-items:center;gap:10px;
  background:var(--bg3);border:1px solid var(--border);
  border-radius:99px;padding:8px 16px;
  cursor:pointer;transition:border-color .2s,background .2s;
  min-width:200px;
}
.nav-search-bar:hover{border-color:var(--cyan);background:var(--dim);}
.nav-search-bar svg{width:15px;height:15px;color:var(--muted);}
.nav-search-bar span{font-size:12px;color:var(--muted);flex:1;}
.nav-search-bar kbd{
  font-family:inherit;font-size:10px;color:var(--muted);
  background:var(--dim);border:1px solid var(--border2);
  border-radius:4px;padding:2px 6px;
}
.nav-sign-btn{
  display:flex;align-items:center;gap:6px;
  padding:9px 22px;border-radius:99px;
  font-size:13px;font-weight:700;
  background:var(--accent);color:#fff;
  transition:background .2s,transform .15s,box-shadow .2s;
  box-shadow:0 0 20px rgba(232,23,63,.35);
}
.nav-sign-btn:hover{background:var(--accent2);transform:translateY(-1px);}
@media(max-width:768px){
  .nav-links,.nav-search-bar,.nav-sign-btn{display:none;}
  .nav-right .mobile-search-btn{display:flex;}
  #topnav{padding:0 20px;}
  .vx-logo{margin-right:0;}
}
.mobile-search-btn{
  display:none;
  width:38px;height:38px;border-radius:50%;
  background:var(--bg3);border:1px solid var(--border);
  align-items:center;justify-content:center;
  color:var(--muted);transition:color .2s,border-color .2s;
}
.mobile-search-btn svg{width:16px;height:16px;}

/* ═══════════════════════════════════════════════════════
   BOTTOM NAVIGATION — MOBILE
═══════════════════════════════════════════════════════ */
#botnav{
  display:none;
  position:fixed;bottom:0;left:0;right:0;z-index:200;
  height:var(--bot-nav-h);
  background:rgba(6,9,16,.97);
  backdrop-filter:blur(24px);
  border-top:1px solid var(--border);
  padding:0 4px;
}
@media(max-width:768px){#botnav{display:flex;align-items:stretch;}}
.bot-nav-item{
  flex:1;display:flex;flex-direction:column;
  align-items:center;justify-content:center;gap:4px;
  padding:8px 4px;
  color:var(--muted);font-size:10px;font-weight:600;
  letter-spacing:.3px;
  transition:color .2s;
  border-radius:10px;
  -webkit-tap-highlight-color:transparent;
}
.bot-nav-item svg{width:20px;height:20px;}
.bot-nav-item.active{color:var(--accent);}
.bot-nav-item.home-btn .home-icon-wrap{
  width:44px;height:44px;border-radius:14px;
  background:linear-gradient(135deg,var(--accent),#9b14cf);
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 4px 16px rgba(232,23,63,.5);
  margin-top:-10px;
}
.bot-nav-item.home-btn svg{width:22px;height:22px;color:#fff;}

/* ═══════════════════════════════════════════════════════
   SEARCH OVERLAY
═══════════════════════════════════════════════════════ */
#search-overlay{
  position:fixed;inset:0;z-index:500;
  background:rgba(6,9,16,.97);backdrop-filter:blur(24px);
  display:flex;flex-direction:column;align-items:center;
  padding-top:100px;
  opacity:0;pointer-events:none;transition:opacity .3s;
}
#search-overlay.open{opacity:1;pointer-events:all;}
.so-inner{width:100%;max-width:680px;padding:0 24px;}
.so-box{
  display:flex;align-items:center;gap:14px;
  border-bottom:2px solid var(--cyan);padding-bottom:14px;
}
.so-box svg{width:26px;height:26px;color:var(--cyan);flex-shrink:0;}
.so-box input{
  flex:1;background:none;border:none;outline:none;
  font-family:'Plus Jakarta Sans',sans-serif;
  font-size:26px;font-weight:300;
  color:var(--text);caret-color:var(--cyan);
}
.so-box input::placeholder{color:var(--muted);}
.so-tags{display:flex;gap:8px;margin-top:20px;flex-wrap:wrap;}
.so-tag{
  padding:6px 14px;border-radius:99px;font-size:12px;font-weight:600;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted);cursor:pointer;transition:all .2s;
}
.so-tag:hover{border-color:var(--cyan);color:var(--cyan);}
.so-close{
  position:absolute;top:18px;right:20px;
  width:42px;height:42px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:24px;color:var(--muted);cursor:pointer;
  transition:color .2s,background .2s;
}
.so-close:hover{color:var(--text);background:var(--bg3);}

/* ═══════════════════════════════════════════════════════
   AD PLACEMENTS
═══════════════════════════════════════════════════════ */
.ad-leaderboard{
  background:var(--bg2);
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
  padding:12px 0;
  display:flex;justify-content:center;
}
.ad-box{
  background:var(--bg3);
  border:1px dashed rgba(255,255,255,.12);
  border-radius:var(--r);
  display:flex;flex-direction:column;
  align-items:center;justify-content:center;
  gap:6px;color:var(--muted);
  position:relative;overflow:hidden;
}
.ad-box::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(232,23,63,.03),rgba(0,200,240,.03));
}
.ad-label{
  font-size:9px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:var(--muted);opacity:.7;
  position:relative;z-index:1;
}
.ad-copy{font-size:11px;font-weight:500;color:var(--muted2);position:relative;z-index:1;}
.ad-sub{font-size:10px;color:var(--muted);position:relative;z-index:1;}
.ad-728{width:min(728px,100%);height:90px;}
@media(max-width:768px){.ad-728{height:60px;}}
.ad-300{width:300px;height:250px;flex-shrink:0;}
.ad-300-600{width:300px;height:600px;flex-shrink:0;}
@media(max-width:1200px){.ad-300-600{height:250px;}}
.ad-320{width:100%;max-width:320px;height:50px;}
.ad-native{
  width:100%;height:120px;
  display:flex;align-items:center;gap:20px;
  padding:0 20px;
}
.ad-native-thumb{
  width:80px;height:80px;border-radius:10px;
  background:linear-gradient(135deg,rgba(232,23,63,.15),rgba(139,92,246,.15));
  border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;font-size:28px;
}
.ad-native-body{flex:1;}
.ad-native-ttl{font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px;}
.ad-native-desc{font-size:12px;color:var(--muted2);line-height:1.4;}
.ad-native-cta{
  padding:9px 20px;border-radius:99px;
  font-size:12px;font-weight:700;
  background:var(--accent);color:#fff;
}
.ad-native-cta:hover{background:var(--accent2);}
.ad-inline{padding:20px 0;display:flex;justify-content:center;}

/* ═══════════════════════════════════════════════════════
   LAYOUT UTILS
═══════════════════════════════════════════════════════ */
.container{max-width:1440px;margin:0 auto;padding:0 48px;}
@media(max-width:768px){.container{padding:0 16px;}}

/* ═══════════════════════════════════════════════════════
   ARCHIVE PAGE HERO / BREADCRUMB BANNER
═══════════════════════════════════════════════════════ */
#archive-hero{
  margin-top:var(--nav-h);
  background:var(--bg2);
  border-bottom:1px solid var(--border);
  padding:36px 0 0;
  position:relative;
  overflow:hidden;
}
#archive-hero::before{
  content:'';
  position:absolute;inset:0;
  background:
    radial-gradient(ellipse 60% 120% at 80% 50%, rgba(232,23,63,.07) 0%, transparent 70%),
    radial-gradient(ellipse 40% 80% at 10% 80%, rgba(0,200,240,.05) 0%, transparent 60%);
  pointer-events:none;
}
.arch-hero-inner{
  display:flex;align-items:flex-end;gap:0;
  position:relative;z-index:1;
}
.arch-breadcrumb{
  display:flex;align-items:center;gap:8px;
  font-size:12px;color:var(--muted);font-weight:600;
  letter-spacing:.3px;margin-bottom:12px;
}
.arch-breadcrumb a{color:var(--muted);transition:color .2s;}
.arch-breadcrumb a:hover{color:var(--cyan);}
.arch-breadcrumb span{color:var(--dim);}
.arch-hero-title{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(40px,5vw,68px);
  letter-spacing:2px;line-height:1;
  margin-bottom:10px;
}
.arch-hero-title .accent{color:var(--accent2);}
.arch-hero-meta{
  display:flex;align-items:center;gap:16px;
  font-size:12px;color:var(--muted2);font-weight:600;
  margin-bottom:0;padding-bottom:24px;
  flex-wrap:wrap;
}
.arch-hero-meta.vex-page-controls{padding:20px 0 24px;}
.arch-hero-meta .count{
  font-size:14px;font-weight:800;
  color:var(--text);
}
.arch-hero-meta .pipe{color:var(--dim);}

.arch-quick-genres{
  display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;
  padding-bottom:20px;margin-top:4px;
}
.arch-quick-genres::-webkit-scrollbar{display:none;}
.qg-pill{
  flex-shrink:0;padding:6px 16px;border-radius:99px;
  font-size:12px;font-weight:700;letter-spacing:.3px;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted2);cursor:pointer;
  transition:all .2s;white-space:nowrap;
}
.qg-pill:hover{border-color:var(--accent2);color:var(--accent2);}
.qg-pill.active{
  background:rgba(232,23,63,.15);
  border-color:var(--accent);color:var(--accent2);
}

/* ═══════════════════════════════════════════════════════
   MAIN ARCHIVE LAYOUT
═══════════════════════════════════════════════════════ */
#archive-main{
  padding:32px 0 64px;
}
.archive-layout{
  display:grid;
  grid-template-columns:280px 1fr;
  gap:32px;
  align-items:start;
}
@media(max-width:1024px){
  .archive-layout{grid-template-columns:240px 1fr;gap:24px;}
}
@media(max-width:800px){
  .archive-layout{grid-template-columns:1fr;}
}

/* ═══════════════════════════════════════════════════════
   SIDEBAR — FILTERS
═══════════════════════════════════════════════════════ */
#sidebar{
  position:sticky;top:calc(var(--nav-h) + 16px);
  display:flex;flex-direction:column;gap:6px;
}
@media(max-width:800px){
  #sidebar{
    position:static;
    display:none; /* toggled by mobile filter btn */
  }
  #sidebar.open{display:flex;}
}

.filter-panel{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:14px;overflow:hidden;
}
.fp-head{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 18px;cursor:pointer;
  border-bottom:1px solid transparent;
  transition:border-color .2s;
  user-select:none;
}
.fp-head.open{border-color:var(--border);}
.fp-head-left{display:flex;align-items:center;gap:10px;}
.fp-icon{
  width:28px;height:28px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;
}
.fp-icon svg{width:14px;height:14px;}
.fp-title{font-size:12px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:var(--text);}
.fp-chevron{
  width:18px;height:18px;color:var(--muted);
  transition:transform .25s;flex-shrink:0;
}
.fp-head.open .fp-chevron{transform:rotate(180deg);}
.fp-body{
  padding:16px 18px;
  display:none;
}
.fp-body.open{display:block;}

.filter-search{
  display:flex;align-items:center;gap:10px;
  background:var(--bg3);border:1px solid var(--border);
  border-radius:8px;padding:9px 13px;
  transition:border-color .2s;
}
.filter-search:focus-within{border-color:var(--cyan);}
.filter-search svg{width:14px;height:14px;color:var(--muted);flex-shrink:0;}
.filter-search input{
  background:none;border:none;outline:none;
  font-family:inherit;font-size:13px;color:var(--text);
  width:100%;
}
.filter-search input::placeholder{color:var(--muted);}

.fc-list{display:flex;flex-direction:column;gap:4px;margin-top:12px;}
.fc-item{
  display:flex;align-items:center;gap:10px;
  padding:8px 10px;border-radius:8px;cursor:pointer;
  transition:background .2s;
}
.fc-item:hover{background:rgba(255,255,255,.04);}
.fc-item input[type="checkbox"]{display:none;}
.fc-check{
  width:18px;height:18px;border-radius:5px;
  border:1.5px solid var(--border2);
  background:var(--bg3);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;transition:all .2s;
}
.fc-item input:checked ~ .fc-check{
  background:var(--accent);border-color:var(--accent);
}
.fc-check svg{width:10px;height:10px;color:#fff;opacity:0;transition:opacity .15s;}
.fc-item input:checked ~ .fc-check svg{opacity:1;}
.fc-label{
  flex:1;font-size:13px;font-weight:500;color:var(--muted2);
  transition:color .2s;
}
.fc-item:hover .fc-label{color:var(--text);}
.fc-item input:checked ~ .fc-check + .fc-label,
.fc-item input:checked + .fc-check + .fc-label{color:var(--text);}
.fc-count{font-size:11px;color:var(--muted);font-weight:600;}

.fr-list{display:flex;flex-direction:column;gap:4px;}
.fr-item{
  display:flex;align-items:center;gap:10px;
  padding:8px 10px;border-radius:8px;cursor:pointer;
  transition:background .2s;
}
.fr-item:hover{background:rgba(255,255,255,.04);}
.fr-item input[type="radio"]{display:none;}
.fr-dot{
  width:18px;height:18px;border-radius:50%;
  border:1.5px solid var(--border2);
  background:var(--bg3);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;transition:all .2s;
}
.fr-dot::after{
  content:'';width:8px;height:8px;border-radius:50%;
  background:var(--accent);opacity:0;transition:opacity .15s;
}
.fr-item input:checked ~ .fr-dot{border-color:var(--accent);}
.fr-item input:checked ~ .fr-dot::after{opacity:1;}
.fr-label{font-size:13px;font-weight:500;color:var(--muted2);}
.fr-item input:checked ~ .fr-dot + .fr-label{color:var(--text);}

.rating-range{display:flex;align-items:center;gap:10px;margin-top:8px;}
.rating-range input[type="range"]{
  flex:1;-webkit-appearance:none;
  height:3px;border-radius:99px;
  background:linear-gradient(to right, var(--accent) 0%, var(--accent) var(--pct, 70%), var(--dim) var(--pct, 70%));
  outline:none;cursor:pointer;
}
.rating-range input[type="range"]::-webkit-slider-thumb{
  -webkit-appearance:none;
  width:16px;height:16px;border-radius:50%;
  background:var(--accent);
  border:2px solid var(--bg);
  box-shadow:0 0 8px rgba(232,23,63,.4);
  cursor:pointer;
}
.rating-val{
  font-size:13px;font-weight:800;color:var(--gold);
  white-space:nowrap;
}

.year-range{display:flex;gap:10px;margin-top:8px;}
.year-input{
  flex:1;background:var(--bg3);border:1px solid var(--border);
  border-radius:8px;padding:8px 12px;
  font-family:inherit;font-size:13px;color:var(--text);
  outline:none;transition:border-color .2s;width:100%;
}
.year-input:focus{border-color:var(--cyan);}
.year-sep{color:var(--muted);align-self:center;font-size:12px;}

.ep-chips{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;}
.ep-chip{
  padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted);cursor:pointer;transition:all .2s;
}
.ep-chip:hover{border-color:var(--purple);color:var(--purple);}
.ep-chip.active{background:rgba(139,92,246,.15);border-color:var(--purple);color:var(--purple);}

.tag-cloud{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;}
.tc-tag{
  padding:4px 10px;border-radius:5px;font-size:11px;font-weight:600;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted);cursor:pointer;transition:all .2s;
}
.tc-tag:hover{border-color:var(--cyan);color:var(--cyan);}
.tc-tag.active{background:rgba(0,200,240,.1);border-color:var(--cyan);color:var(--cyan);}

.filter-actions{
  display:flex;gap:8px;margin-top:16px;
  padding-top:16px;border-top:1px solid var(--border);
}
.fa-apply{
  flex:1;padding:10px;border-radius:8px;
  font-size:12px;font-weight:800;
  background:var(--accent);color:#fff;letter-spacing:.5px;
  transition:background .2s;
}
.fa-apply:hover{background:var(--accent2);}
.fa-reset{
  padding:10px 16px;border-radius:8px;
  font-size:12px;font-weight:700;
  border:1px solid var(--border2);color:var(--muted2);
  transition:all .2s;
}
.fa-reset:hover{border-color:var(--accent);color:var(--accent);}

.active-filters{
  display:flex;flex-wrap:wrap;gap:6px;
  margin-bottom:20px;
}
.af-badge{
  display:flex;align-items:center;gap:6px;
  padding:5px 10px;border-radius:6px;
  font-size:11px;font-weight:700;
  background:rgba(232,23,63,.1);
  border:1px solid rgba(232,23,63,.25);
  color:var(--accent2);
}
.af-badge button{
  width:14px;height:14px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  color:var(--accent);opacity:.7;
  font-size:14px;line-height:1;transition:opacity .2s;
}
.af-badge button:hover{opacity:1;}
.af-clear-all{
  padding:5px 12px;border-radius:6px;
  font-size:11px;font-weight:700;
  color:var(--muted);border:1px solid var(--border);
  transition:all .2s;
  white-space:nowrap;
}
.af-clear-all:hover{color:var(--accent);border-color:var(--accent);}

.sidebar-ad{
  background:var(--card);border:1px solid var(--border);
  border-radius:14px;overflow:hidden;
  display:flex;flex-direction:column;align-items:center;
  justify-content:center;
  padding:12px;
}

#results-area{min-width:0;}

.results-toolbar{
  display:flex;align-items:center;gap:12px;
  margin-bottom:24px;flex-wrap:wrap;
}
.results-count{
  font-size:13px;color:var(--muted2);font-weight:600;
  flex:1;white-space:nowrap;
}
.results-count strong{color:var(--text);}
.toolbar-right{display:flex;align-items:center;gap:8px;}

.sort-select{
  display:flex;align-items:center;gap:8px;
  background:var(--bg3);border:1px solid var(--border);
  border-radius:8px;padding:8px 12px;
  font-family:inherit;font-size:12px;font-weight:700;
  color:var(--text);cursor:pointer;
  transition:border-color .2s;
  position:relative;
}
.sort-select svg{width:13px;height:13px;color:var(--muted);}
.sort-select select{
  background:none;border:none;outline:none;
  font-family:inherit;font-size:12px;font-weight:700;
  color:var(--text);cursor:pointer;
  -webkit-appearance:none;
  padding-right:4px;
}
.sort-select select option{background:var(--bg3);color:var(--text);}

.mobile-filter-btn{
  display:none;
  align-items:center;gap:8px;
  padding:8px 16px;border-radius:8px;
  font-size:12px;font-weight:700;
  border:1px solid var(--border2);color:var(--text);
  background:var(--bg3);
  transition:all .2s;
  white-space:nowrap;
}
.mobile-filter-btn svg{width:14px;height:14px;}
.mobile-filter-btn .badge{
  background:var(--accent);color:#fff;
  font-size:10px;font-weight:800;
  padding:1px 6px;border-radius:99px;
}
@media(max-width:800px){.mobile-filter-btn{display:flex;}}

.card-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0, 1fr));
  gap:12px;
}
@media(min-width:560px){
  .card-grid{grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));gap:16px;}
}
@media(min-width:900px){
  .card-grid{grid-template-columns:repeat(auto-fill, minmax(168px, 1fr));gap:20px;}
}
.acard{
  cursor:pointer;
  min-width:0;
  width:100%;
  flex-shrink:initial;
  scroll-snap-align:initial;
  transition:transform .25s;
}
.archive-card-link{
  display:block;
  color:inherit;
  text-decoration:none;
}
.acard:hover{transform:translateY(-6px);}
.acard-thumb{
  position:relative;border-radius:var(--r);overflow:hidden;
  aspect-ratio:2/3;background:var(--bg3);margin-bottom:10px;
  border:1px solid transparent;
  transition:border-color .25s,box-shadow .25s;
}
.acard-thumb::after{
  content:"";position:absolute;left:0;right:0;bottom:0;height:2px;
  background:linear-gradient(to right,var(--gold),var(--accent));
  transform:scaleX(0);transform-origin:left;transition:transform .35s;z-index:3;
}
.acard:hover .acard-thumb{border-color:rgba(255,195,64,.28);box-shadow:0 16px 34px rgba(0,0,0,.28);}
.acard:hover .acard-thumb::after{transform:scaleX(1);}
.acard-ph{
  width:100%;height:100%;
  display:flex;align-items:center;justify-content:center;
  font-family:'Bebas Neue',sans-serif;
  font-size:11px;letter-spacing:1px;
  color:rgba(255,255,255,.25);text-align:center;padding:8px;
  position:relative;
}
.acard-thumb img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
  transition:transform .5s,filter .35s;
}
.acard:hover .acard-thumb img{transform:scale(1.08);filter:saturate(1.08) brightness(.78);}
.acard-overlay{
  position:absolute;inset:0;
  background:linear-gradient(180deg,transparent 40%,rgba(6,9,16,.95) 100%);
  opacity:0;transition:opacity .3s;
  display:flex;flex-direction:column;
  align-items:center;justify-content:flex-end;padding:10px;
}
.acard:hover .acard-overlay{opacity:1;}
.acard-play{
  width:44px;height:44px;border-radius:50%;
  background:var(--accent);
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 0 20px rgba(232,23,63,.6);
  margin-bottom:8px;
}
.acard-play svg{width:16px;height:16px;margin-left:2px;}
.acard-overlay-tags{display:flex;gap:4px;}
.acard-badge{
  position:absolute;top:8px;left:8px;
  padding:3px 7px;border-radius:5px;
  font-size:10px;font-weight:700;letter-spacing:.4px;
}
.badge-new{background:var(--accent);color:#fff;}
.badge-ep{background:rgba(0,0,0,.75);color:var(--text);border:1px solid var(--border);}
.badge-hot{background:var(--gold);color:#000;}
.badge-sub{background:rgba(0,200,240,.2);color:var(--cyan);border:1px solid rgba(0,200,240,.4);}
.badge-dub{background:rgba(255,195,64,.15);color:var(--gold);border:1px solid rgba(255,195,64,.35);}
.acard-score{
  position:absolute;top:8px;right:8px;
  display:flex;align-items:center;gap:3px;
  font-size:10px;font-weight:700;
  background:rgba(0,0,0,.75);padding:3px 7px;border-radius:5px;
  color:var(--gold);
}
.acard-score svg{width:10px;height:10px;}
.acard-watchlist{
  position:absolute;bottom:8px;right:8px;
  width:30px;height:30px;border-radius:50%;
  background:rgba(0,0,0,.7);border:1px solid var(--border2);
  display:flex;align-items:center;justify-content:center;
  opacity:0;transition:opacity .2s,background .2s;
}
.acard:hover .acard-watchlist{opacity:1;}
.acard-watchlist:hover{background:var(--accent);}
.acard-watchlist svg{width:13px;height:13px;}
.acard-title{
  font-size:12px;font-weight:700;line-height:1.3;margin-bottom:4px;
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.acard-meta{
  font-size:11px;color:var(--muted2);
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  min-width:0;
}
.acard-meta span{
  min-width:0;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.acard-desc{display:none;}
.acard-dot{display:none;}
.tag-s{padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;background:rgba(0,200,240,.15);color:var(--cyan);border:1px solid rgba(0,200,240,.3);}
.tag-d{padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;background:rgba(255,195,64,.1);color:var(--gold);border:1px solid rgba(255,195,64,.3);}

.acard-list{
  display:flex;gap:16px;padding:14px;
  background:var(--card);border:1px solid var(--border);
  border-radius:var(--r);cursor:pointer;
  transition:border-color .2s,transform .2s;
  align-items:center;
}
.acard-list:hover{border-color:rgba(232,23,63,.25);transform:translateX(4px);}
.acard-list .list-thumb{
  width:70px;height:90px;border-radius:8px;
  overflow:hidden;flex-shrink:0;background:var(--bg3);
  position:relative;
}
.acard-list .list-thumb .acard-ph{font-size:8px;padding:4px;}
.acard-list .list-info{flex:1;min-width:0;}
.acard-list .list-title{font-size:14px;font-weight:700;margin-bottom:5px;line-height:1.3;}
.acard-list .list-meta{
  display:flex;align-items:center;gap:8px;
  font-size:11px;color:var(--muted2);flex-wrap:wrap;margin-bottom:8px;
}
.acard-list .list-desc{
  font-family:'Crimson Pro',serif;
  font-style:italic;font-size:13px;color:var(--muted2);
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
  line-height:1.5;
}
.acard-list .list-right{
  display:flex;flex-direction:column;align-items:flex-end;gap:8px;
  flex-shrink:0;
}
.acard-list .list-score{
  display:flex;align-items:center;gap:4px;
  font-size:15px;font-weight:800;color:var(--gold);
}
.acard-list .list-score svg{width:13px;height:13px;}
.acard-list .list-actions{display:flex;gap:6px;}
.la-play{
  width:34px;height:34px;border-radius:50%;
  background:var(--accent);color:#fff;
  display:flex;align-items:center;justify-content:center;
  transition:background .2s,transform .15s;
}
.la-play:hover{background:var(--accent2);transform:scale(1.08);}
.la-play svg{width:14px;height:14px;margin-left:1px;}
.la-add{
  width:34px;height:34px;border-radius:50%;
  border:1px solid var(--border2);color:var(--muted2);
  display:flex;align-items:center;justify-content:center;
  transition:all .2s;
}
.la-add:hover{border-color:var(--cyan);color:var(--cyan);}
.la-add svg{width:14px;height:14px;}

.archive-empty{
  display:grid;
  justify-items:center;
  gap:8px;
  padding:42px 18px;
  margin-top:18px;
  border:1px solid var(--border);
  border-radius:var(--r);
  background:var(--card);
  color:var(--muted2);
  text-align:center;
}
.archive-empty strong{color:var(--text);}
.archive-empty[hidden]{display:none;}

.pagination{
  display:flex;align-items:center;justify-content:center;
  gap:6px;margin-top:40px;flex-wrap:wrap;
}
.pg-btn{
  width:38px;height:38px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  font-size:13px;font-weight:700;
  border:1px solid var(--border);background:var(--bg3);
  color:var(--muted2);cursor:pointer;
  transition:all .2s;
}
.pg-btn:hover{border-color:var(--accent);color:var(--accent);}
.pg-btn.active{background:var(--accent);border-color:var(--accent);color:#fff;}
.pg-btn.disabled{opacity:.4;pointer-events:none;}
.pg-btn svg{width:14px;height:14px;}
.pg-dots{
  width:38px;height:38px;display:flex;align-items:center;justify-content:center;
  font-size:13px;color:var(--muted);letter-spacing:2px;
}
.pg-jump{
  display:flex;align-items:center;gap:8px;
  font-size:12px;color:var(--muted2);margin-left:8px;
}
.pg-jump input{
  width:52px;padding:8px 10px;border-radius:8px;
  background:var(--bg3);border:1px solid var(--border);
  font-family:inherit;font-size:12px;color:var(--text);
  outline:none;text-align:center;
  transition:border-color .2s;
}
.pg-jump input:focus{border-color:var(--cyan);}
.pg-jump-go{
  padding:8px 14px;border-radius:8px;
  font-size:11px;font-weight:700;
  background:var(--bg3);border:1px solid var(--border);
  color:var(--muted2);cursor:pointer;transition:all .2s;
}
.pg-jump-go:hover{border-color:var(--accent);color:var(--accent);}

.no-results{
  display:flex;flex-direction:column;align-items:center;
  padding:80px 20px;text-align:center;
  color:var(--muted2);
}
.no-results svg{width:56px;height:56px;color:var(--dim);margin-bottom:16px;}
.no-results h3{font-family:'Bebas Neue',sans-serif;font-size:28px;letter-spacing:2px;color:var(--muted);margin-bottom:8px;}
.no-results p{font-size:13px;max-width:320px;line-height:1.6;}

.grid-ad-row{
  grid-column:1/-1;
  padding:4px 0;
}

.c1{background:linear-gradient(135deg,#1a0a2e,#2d1254);}
.c2{background:linear-gradient(135deg,#0a1a2e,#123060);}
.c3{background:linear-gradient(135deg,#1a0a0a,#3a1010);}
.c4{background:linear-gradient(135deg,#0a1a0a,#103a10);}
.c5{background:linear-gradient(135deg,#1a150a,#3a2a0a);}
.c6{background:linear-gradient(135deg,#0a1a1a,#0a3030);}
.c7{background:linear-gradient(135deg,#1a0a1a,#30103a);}
.c8{background:linear-gradient(135deg,#0a0a1a,#101038);}

#toast{
  position:fixed;bottom:calc(var(--bot-nav-h) + 16px);left:50%;
  transform:translateX(-50%) translateY(20px);
  background:var(--bg3);border:1px solid var(--border2);
  border-radius:99px;padding:10px 20px;
  font-size:13px;font-weight:600;
  opacity:0;pointer-events:none;transition:all .3s;
  white-space:nowrap;z-index:999;
  box-shadow:0 8px 32px rgba(0,0,0,.5);
}
#toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
@media(min-width:769px){#toast{bottom:24px;}}

#filter-drawer{
  display:none;
  position:fixed;inset:0;z-index:400;
}
#filter-drawer.open{display:block;}
.fd-backdrop{
  position:absolute;inset:0;
  background:rgba(6,9,16,.85);backdrop-filter:blur(10px);
}
.fd-panel{
  position:absolute;left:0;top:0;bottom:0;
  width:min(320px,90vw);
  background:var(--bg2);
  border-right:1px solid var(--border);
  overflow-y:auto;
  padding:20px 16px;
  display:flex;flex-direction:column;gap:6px;
}
.fd-head{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:16px;
}
.fd-head h3{
  font-family:'Bebas Neue',sans-serif;
  font-size:22px;letter-spacing:2px;
}
.fd-close{
  width:36px;height:36px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;
  border:1px solid var(--border);color:var(--muted);
  transition:all .2s;
}
.fd-close:hover{border-color:var(--accent);color:var(--accent);}
.fd-close svg{width:16px;height:16px;}

#archive-hero.vex-page-hero{padding:40px 0 0;margin-top:var(--nav-h);}
</style>
