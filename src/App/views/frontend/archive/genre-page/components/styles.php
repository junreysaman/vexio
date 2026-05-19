<style>
#genre-page{
  background:var(--bg);
  min-height:100vh;
  padding-bottom:72px;
}
.genre-shell{
  max-width:1440px;
  margin:0 auto;
  padding:0 48px;
}
@media(max-width:768px){.genre-shell{padding:0 16px;}}

#genre-hero{
  margin-top:var(--nav-h);
  background:var(--bg2);
  border-bottom:1px solid var(--border);
  padding:40px 0 0;
  position:relative;
  overflow:hidden;
}
#genre-hero::before{
  content:'';
  position:absolute;
  inset:0;
  background:
    radial-gradient(ellipse 60% 120% at 80% 50%,rgba(232,23,63,.08) 0%,transparent 70%),
    radial-gradient(ellipse 40% 80% at 10% 80%,rgba(139,92,246,.06) 0%,transparent 60%),
    radial-gradient(ellipse 30% 60% at 50% 0%,rgba(0,200,240,.04) 0%,transparent 60%);
  pointer-events:none;
}
#genre-hero::after{
  content:'';
  position:absolute;
  top:0;
  left:0;
  right:0;
  height:3px;
  background:linear-gradient(90deg,transparent,var(--accent),var(--purple),var(--cyan),transparent);
  opacity:.6;
}
.genre-hero-inner{position:relative;z-index:1;}
.arch-breadcrumb{
  display:flex;
  align-items:center;
  gap:8px;
  font-size:12px;
  color:var(--muted);
  font-weight:700;
  letter-spacing:.3px;
  margin-bottom:14px;
}
.arch-breadcrumb a{color:var(--muted);transition:color .2s;}
.arch-breadcrumb a:hover{color:var(--cyan);}
.arch-breadcrumb span{color:var(--muted2);}
.genre-hero-top{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:24px;
  flex-wrap:wrap;
  margin-bottom:6px;
}
.genre-hero-title{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(42px,6vw,76px);
  letter-spacing:2px;
  line-height:.95;
}
.genre-hero-title .accent{color:var(--accent2);}
.genre-hero-sub{
  font-size:13px;
  color:var(--muted2);
  font-weight:600;
  margin-top:10px;
  line-height:1.5;
  max-width:520px;
}
.genre-count-pill{
  display:flex;
  align-items:center;
  gap:8px;
  background:rgba(232,23,63,.1);
  border:1px solid rgba(232,23,63,.2);
  border-radius:99px;
  padding:8px 20px;
  font-size:12px;
  font-weight:800;
  color:var(--accent2);
  white-space:nowrap;
  flex-shrink:0;
  align-self:flex-start;
  margin-top:8px;
}
.genre-count-pill svg{width:13px;height:13px;}
.genre-search-row{
  display:flex;
  align-items:center;
  gap:12px;
  padding:20px 0 24px;
  flex-wrap:wrap;
}
.genre-search-input{
  display:flex;
  align-items:center;
  gap:10px;
  background:var(--bg3);
  border:1px solid var(--border);
  border-radius:10px;
  padding:10px 16px;
  flex:1;
  min-width:200px;
  max-width:360px;
  transition:border-color .2s;
}
.genre-search-input:focus-within{border-color:var(--cyan);}
.genre-search-input svg{width:15px;height:15px;color:var(--muted);flex-shrink:0;}
.genre-search-input input{
  background:none;
  border:none;
  outline:none;
  font-family:inherit;
  font-size:13px;
  color:var(--text);
  width:100%;
}
.genre-filter-pills{
  display:flex;
  gap:8px;
  overflow-x:auto;
  scrollbar-width:none;
  flex:1;
}
.genre-filter-pills::-webkit-scrollbar{display:none;}
.gf-pill{
  flex-shrink:0;
  padding:7px 16px;
  border-radius:99px;
  font-size:12px;
  font-weight:800;
  letter-spacing:.3px;
  border:1px solid var(--border);
  background:var(--bg3);
  color:var(--muted2);
  cursor:pointer;
  transition:all .2s;
  white-space:nowrap;
}
.gf-pill:hover{border-color:var(--accent2);color:var(--accent2);}
.gf-pill.active{
  background:rgba(232,23,63,.15);
  border-color:var(--accent);
  color:var(--accent2);
}

#genre-main{padding:40px 0 80px;}
.genre-section{margin-bottom:52px;}
.active-genre-results{
  scroll-margin-top:calc(var(--nav-h) + 24px);
}
.section-header{
  display:flex;
  align-items:center;
  gap:14px;
  margin-bottom:24px;
}
.section-line{
  flex:1;
  height:1px;
  background:linear-gradient(90deg,var(--border2),transparent);
}
.section-title{
  font-family:'Bebas Neue',sans-serif;
  font-size:13px;
  letter-spacing:2.5px;
  color:var(--muted);
  white-space:nowrap;
}
.section-dot{
  width:5px;
  height:5px;
  border-radius:50%;
  background:var(--accent);
  flex-shrink:0;
}
.section-dot-cyan{background:var(--cyan);}
.section-dot-gold{background:var(--gold);}

.stats-banner{
  background:var(--bg2);
  border:1px solid var(--border);
  border-radius:14px;
  padding:24px 32px;
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:24px;
  margin-bottom:48px;
  position:relative;
  overflow:hidden;
}
.stats-banner::before{
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(135deg,rgba(232,23,63,.04),rgba(139,92,246,.04));
  pointer-events:none;
}
@media(max-width:600px){.stats-banner{grid-template-columns:repeat(2,1fr);padding:20px;gap:20px 10px;}}
.stat-item{text-align:center;position:relative;z-index:1;}
.stat-item + .stat-item::before{
  content:'';
  position:absolute;
  left:0;
  top:20%;
  bottom:20%;
  width:1px;
  background:var(--border);
}
@media(max-width:600px){.stat-item:nth-child(odd)::before{display:none;}}
.stat-number{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(28px,4vw,40px);
  letter-spacing:1px;
  background:linear-gradient(135deg,var(--accent2),var(--accent));
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  line-height:1;
}
.stat-number-cyan{background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.stat-number-gold{background:linear-gradient(135deg,var(--gold),#ff9640);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.stat-label{
  font-size:11px;
  font-weight:800;
  letter-spacing:1px;
  text-transform:uppercase;
  color:var(--muted);
  margin-top:4px;
}

.genre-grid-featured{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:20px;
}
@media(max-width:768px){.genre-grid-featured{grid-template-columns:1fr;}}
.genre-card-large{
  position:relative;
  overflow:hidden;
  border-radius:16px;
  aspect-ratio:16/9;
  cursor:pointer;
  color:inherit;
  text-decoration:none;
  display:block;
}
.genre-card-large::before{
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(135deg,rgba(6,9,16,.2) 0%,rgba(6,9,16,.05) 40%,rgba(6,9,16,.6) 100%);
  z-index:1;
  transition:opacity .4s;
}
.genre-card-large:hover::before{opacity:.7;}
.gcl-bg{
  position:absolute;
  inset:0;
  background-size:cover;
  background-position:center;
  transition:transform .6s cubic-bezier(.25,.46,.45,.94),filter .4s;
  filter:saturate(.9) brightness(.85);
}
.gcl-bg.is-empty,.gc-bg.is-empty,.gcs-bg.is-empty{
  background:
    radial-gradient(circle at 25% 18%,rgba(232,23,63,.28),transparent 34%),
    radial-gradient(circle at 78% 78%,rgba(0,200,240,.16),transparent 32%),
    var(--bg3);
}
.genre-card-large:hover .gcl-bg{transform:scale(1.06);filter:saturate(1.1) brightness(.7);}
.gcl-gradient{
  position:absolute;
  inset:0;
  z-index:2;
  background:linear-gradient(to top,rgba(6,9,16,.98) 0%,rgba(6,9,16,.7) 35%,rgba(6,9,16,.1) 70%,transparent 100%);
}
.gcl-content{
  position:absolute;
  bottom:0;
  left:0;
  right:0;
  z-index:3;
  padding:28px 28px 24px;
}
.gcl-kicker{
  color:var(--accent2);
  font-size:11px;
  font-weight:900;
  letter-spacing:1px;
  text-transform:uppercase;
  margin-bottom:8px;
}
.gcl-name{
  font-family:'Bebas Neue',sans-serif;
  font-size:clamp(28px,3.5vw,42px);
  letter-spacing:2px;
  line-height:1;
  margin-bottom:6px;
  color:#fff;
}
.gcl-meta{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
  font-size:12px;
  font-weight:700;
  color:rgba(255,255,255,.58);
  margin-bottom:14px;
}
.gcl-meta .count{color:var(--accent2);font-size:13px;font-weight:900;}
.gcl-model{
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  max-width:240px;
}
.gcl-tags{display:flex;gap:6px;flex-wrap:wrap;}
.gcl-tag{
  padding:3px 10px;
  border-radius:5px;
  font-size:10px;
  font-weight:800;
  letter-spacing:.3px;
  background:rgba(255,255,255,.1);
  border:1px solid rgba(255,255,255,.15);
  color:rgba(255,255,255,.72);
  backdrop-filter:blur(4px);
}
.gcl-btn{
  position:absolute;
  top:20px;
  right:20px;
  z-index:3;
  width:38px;
  height:38px;
  border-radius:50%;
  background:rgba(0,0,0,.5);
  border:1px solid rgba(255,255,255,.15);
  display:flex;
  align-items:center;
  justify-content:center;
  color:rgba(255,255,255,.75);
  backdrop-filter:blur(8px);
  transition:all .25s;
  opacity:0;
  transform:translateY(-4px);
}
.genre-card-large:hover .gcl-btn{opacity:1;transform:translateY(0);}
.gcl-btn svg{width:14px;height:14px;}
.gcl-corner{
  position:absolute;
  top:0;
  left:0;
  z-index:3;
  width:0;
  height:0;
  border-style:solid;
  border-width:48px 48px 0 0;
  border-color:transparent;
  transition:border-color .3s;
}
.genre-card-large:hover .gcl-corner{border-color:rgba(232,23,63,.6) transparent transparent transparent;}
.gcl-corner-label{
  position:absolute;
  top:6px;
  left:4px;
  z-index:4;
  font-family:'Bebas Neue',sans-serif;
  font-size:9px;
  letter-spacing:1.5px;
  color:transparent;
  transition:color .3s;
  transform:rotate(-45deg) translate(-6px,2px);
  pointer-events:none;
}
.genre-card-large:hover .gcl-corner-label{color:#fff;}

.genre-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
  gap:16px;
}
@media(max-width:600px){.genre-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}}
.genre-card{
  position:relative;
  overflow:hidden;
  border-radius:12px;
  aspect-ratio:4/3;
  cursor:pointer;
  border:1px solid var(--border);
  transition:border-color .3s,transform .3s;
  color:inherit;
  text-decoration:none;
  display:block;
}
.genre-card:hover{border-color:rgba(232,23,63,.3);transform:translateY(-4px);}
.gc-bg{
  position:absolute;
  inset:0;
  background-size:cover;
  background-position:center;
  transition:transform .5s cubic-bezier(.25,.46,.45,.94),filter .4s;
  filter:saturate(.85) brightness(.75);
}
.genre-card:hover .gc-bg{transform:scale(1.08);filter:saturate(1.1) brightness(.55);}
.gc-gradient{
  position:absolute;
  inset:0;
  background:linear-gradient(to top,rgba(6,9,16,.97) 0%,rgba(6,9,16,.6) 40%,rgba(6,9,16,.15) 70%,transparent 100%);
  z-index:1;
}
.gc-noise{
  position:absolute;
  inset:0;
  z-index:2;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
  opacity:.3;
  pointer-events:none;
}
.gc-content{
  position:absolute;
  bottom:0;
  left:0;
  right:0;
  z-index:3;
  padding:16px 16px 14px;
  transition:transform .3s;
}
.genre-card:hover .gc-content{transform:translateY(-4px);}
.gc-name{
  font-family:'Bebas Neue',sans-serif;
  font-size:22px;
  letter-spacing:1.5px;
  line-height:1;
  color:#fff;
  margin-bottom:4px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.gc-count{
  font-size:11px;
  font-weight:800;
  color:rgba(255,255,255,.48);
}
.gc-count strong{color:var(--accent2);}
.gc-hover-strip{
  position:absolute;
  bottom:0;
  left:0;
  right:0;
  z-index:4;
  background:linear-gradient(to right,var(--accent),var(--purple));
  height:2px;
  transform:scaleX(0);
  transform-origin:left;
  transition:transform .35s cubic-bezier(.25,.46,.45,.94);
}
.genre-card:hover .gc-hover-strip{transform:scaleX(1);}
.gc-overlay-play{
  position:absolute;
  inset:0;
  z-index:3;
  display:flex;
  align-items:center;
  justify-content:center;
  opacity:0;
  transition:opacity .3s;
}
.genre-card:hover .gc-overlay-play{opacity:1;}
.gc-play-ring{
  width:48px;
  height:48px;
  border-radius:50%;
  background:rgba(232,23,63,.85);
  border:2px solid rgba(255,255,255,.25);
  display:flex;
  align-items:center;
  justify-content:center;
  backdrop-filter:blur(4px);
  transform:scale(.8);
  transition:transform .3s;
  box-shadow:0 0 30px rgba(232,23,63,.5);
}
.genre-card:hover .gc-play-ring{transform:scale(1);}
.gc-play-ring svg{width:16px;height:16px;color:#fff;margin-left:2px;}
.gc-badge{
  position:absolute;
  top:10px;
  right:10px;
  z-index:4;
  padding:3px 8px;
  border-radius:5px;
  font-size:9px;
  font-weight:900;
  letter-spacing:.8px;
  text-transform:uppercase;
  backdrop-filter:blur(8px);
}
.genre-card-large > .gc-badge{top:16px;left:16px;right:auto;}
.gc-badge.hot{background:rgba(255,195,64,.2);color:var(--gold);border:1px solid rgba(255,195,64,.4);}
.gc-badge.new{background:rgba(232,23,63,.2);color:var(--accent2);border:1px solid rgba(232,23,63,.35);}
.gc-badge.top{background:rgba(139,92,246,.2);color:#b78bff;border:1px solid rgba(139,92,246,.35);}

.genre-grid-compact{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
  gap:10px;
}
@media(max-width:600px){.genre-grid-compact{grid-template-columns:repeat(2,minmax(0,1fr));}}
.genre-card-sm{
  position:relative;
  overflow:hidden;
  border-radius:10px;
  height:80px;
  cursor:pointer;
  border:1px solid var(--border);
  display:flex;
  align-items:flex-end;
  transition:border-color .25s,transform .25s;
  color:inherit;
  text-decoration:none;
}
.genre-card-sm:hover{border-color:rgba(0,200,240,.25);transform:translateY(-2px);}
.gcs-bg{
  position:absolute;
  inset:0;
  background-size:cover;
  background-position:center;
  filter:saturate(.7) brightness(.5);
  transition:transform .4s,filter .3s;
}
.genre-card-sm:hover .gcs-bg{transform:scale(1.06);filter:saturate(1) brightness(.4);}
.gcs-grad{
  position:absolute;
  inset:0;
  background:linear-gradient(to right,rgba(6,9,16,.9) 0%,rgba(6,9,16,.2) 100%);
  z-index:1;
}
.gcs-content{
  position:relative;
  z-index:2;
  padding:0 14px 10px;
  width:100%;
}
.gcs-name{
  font-family:'Bebas Neue',sans-serif;
  font-size:17px;
  letter-spacing:1.5px;
  color:#fff;
  line-height:1;
  margin-bottom:2px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.gcs-count{font-size:10px;font-weight:800;color:rgba(255,255,255,.46);}
.gcs-count strong{color:var(--cyan);}
.gcs-accent-line{
  position:absolute;
  left:0;
  top:0;
  bottom:0;
  width:3px;
  z-index:2;
  background:linear-gradient(to bottom,var(--cyan),transparent);
  transform:scaleY(0);
  transform-origin:bottom;
  transition:transform .3s;
}
.genre-card-sm:hover .gcs-accent-line{transform:scaleY(1);}

.title-card-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(170px,1fr));
  gap:20px;
}
@media(max-width:520px){.title-card-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}}
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
  position:relative;
  border-radius:var(--r);
  overflow:hidden;
  aspect-ratio:2/3;
  background:var(--bg3);
  margin-bottom:10px;
  border:1px solid transparent;
  transition:border-color .25s,box-shadow .25s;
}
.acard-thumb::after{
  content:"";
  position:absolute;left:0;right:0;bottom:0;height:2px;
  background:linear-gradient(to right,var(--gold),var(--accent));
  transform:scaleX(0);
  transform-origin:left;
  transition:transform .35s;
  z-index:3;
}
.acard:hover .acard-thumb{border-color:rgba(255,195,64,.28);box-shadow:0 16px 34px rgba(0,0,0,.28);}
.acard:hover .acard-thumb::after{transform:scaleX(1);}
.acard-ph{
  width:100%;
  height:100%;
  display:flex;
  align-items:center;
  justify-content:center;
  font-family:'Bebas Neue',sans-serif;
  font-size:11px;
  letter-spacing:1px;
  color:rgba(255,255,255,.25);
  text-align:center;
  padding:8px;
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
  position:absolute;
  inset:0;
  background:linear-gradient(180deg,transparent 40%,rgba(6,9,16,.95) 100%);
  opacity:0;
  transition:opacity .3s;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:flex-end;
  padding:10px;
}
.acard:hover .acard-overlay{opacity:1;}
.acard-play{
  width:44px;
  height:44px;
  border-radius:50%;
  background:var(--accent);
  display:flex;
  align-items:center;
  justify-content:center;
  box-shadow:0 0 20px rgba(232,23,63,.6);
  margin-bottom:8px;
}
.acard-play svg{width:16px;height:16px;margin-left:2px;}
.acard-badge{
  position:absolute;
  top:8px;
  left:8px;
  padding:3px 7px;
  border-radius:5px;
  font-size:10px;
  font-weight:800;
  letter-spacing:.4px;
}
.badge-ep{background:rgba(0,0,0,.75);color:var(--text);border:1px solid var(--border);}
.acard-score{
  position:absolute;
  top:8px;
  right:8px;
  display:flex;
  align-items:center;
  gap:3px;
  font-size:10px;
  font-weight:800;
  background:rgba(0,0,0,.75);
  padding:3px 7px;
  border-radius:5px;
  color:var(--gold);
}
.acard-score svg{width:10px;height:10px;}
.acard-title{
  font-size:12px;
  font-weight:800;
  line-height:1.3;
  margin-bottom:4px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.acard-meta{
  font-size:11px;
  color:var(--muted2);
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  min-width:0;
}
.acard-meta span{
  min-width:0;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.genre-empty{
  display:grid;
  justify-items:center;
  gap:8px;
  padding:44px 18px;
  border:1px solid var(--border);
  border-radius:14px;
  background:var(--card);
  text-align:center;
  color:var(--muted2);
}
.genre-empty strong{color:var(--text);}

@keyframes fadeUp{
  from{opacity:0;transform:translateY(24px);}
  to{opacity:1;transform:translateY(0);}
}
.genre-card,.genre-card-large,.genre-card-sm,.stats-banner{
  animation:fadeUp .5s ease both;
}

#genre-hero.vex-page-hero{padding:40px 0 0;margin-top:var(--nav-h);}
.genre-search-row.vex-page-controls{padding:20px 0 24px;}
.genre-count-pill.vex-page-pill{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.25);color:var(--green);}
</style>
