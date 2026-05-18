/* ── Film holes ── */
function buildHoles(id, n) {
    const el = document.getElementById(id);
    for (let i = 0; i < n; i++) { const d = document.createElement('div'); d.className = 'fh'; el.appendChild(d); }
}
buildHoles('fh1', 60); buildHoles('fh2', 60);
document.getElementById('yr').textContent = new Date().getFullYear();

/* ── Timecode ── */
let frame = 0, tcRunning = true;
function updateTC() {
    if (!tcRunning) return;
    const f = frame % 30, s = Math.floor(frame / 30) % 60, m = Math.floor(frame / 1800) % 60, h = Math.floor(frame / 108000);
    const pad = n => String(n).padStart(2, '0');
    document.getElementById('tc').textContent = `${pad(h)}:${pad(m)}:${pad(s)}:${pad(f)}`;
    frame++; requestAnimationFrame(updateTC);
}
updateTC();

/* ── Progress + dismiss ── */
const statusMsgs = ['Initializing…', 'Loading assets…', 'Fetching titles…', 'Almost ready…', 'Welcome!'];
const fill = document.getElementById('lfill');
const pctEl = document.getElementById('lpct');
const msgEl = document.getElementById('lmsg');
const loader = document.getElementById('loader');
const app = document.getElementById('app');

let loadStart = null;
const LOAD_DUR = 3200;

function easeProgress(t) {
    if (t < .55) return (t / .55) * 80;
    if (t < .80) return 80 + (t - .55) / .25 * 18;
    return 98 + (t - .80) / .20 * 2;
}

function animateLoader(ts) {
    if (!loadStart) loadStart = ts;
    const t = Math.min((ts - loadStart) / LOAD_DUR, 1);
    const pct = Math.min(Math.round(easeProgress(t)), 100);

    fill.style.width = pct + '%';
    pctEl.textContent = pct + '%';
    msgEl.textContent = statusMsgs[Math.min(Math.floor(t * 4), 4)];

    if (t < 1) {
        requestAnimationFrame(animateLoader);
    } else {
        /* short pause at 100% then reveal app */
        setTimeout(() => {
            tcRunning = false;
            loader.classList.add('hidden');
            app.classList.add('visible');
            document.body.classList.add('loaded');
        }, 420);
    }
}
requestAnimationFrame(animateLoader);