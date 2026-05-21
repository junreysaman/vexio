document.addEventListener('DOMContentLoaded', () => {
    initMediaImageSkeletons();
    initSlider();
    initTrendTabs();
    initScheduleTabs();
    initLazySkeletons();
    initNav();
    initInterstitialAd();
    initStickyMobileAd();
    if (typeof initSearch === 'function') initSearch();
});

let currentSlide = 0;
let slideTimer;
const SLIDE_DUR = 6000;

function initSlider() {
    const hero = document.getElementById('hero');
    const track = document.getElementById('slideTrack');
    const dots = Array.from(document.querySelectorAll('.hero-dot'));
    const slides = Array.from(document.querySelectorAll('.slide'));
    const prev = document.getElementById('heroPrev');
    const next = document.getElementById('heroNext');

    if (!hero || !track || !slides.length) return;

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => goSlide(index));
    });

    if (prev) prev.addEventListener('click', prevSlide);
    if (next) next.addEventListener('click', nextSlide);

    goSlide(0);

    hero.addEventListener('mouseenter', () => {
        clearTimeout(slideTimer);
        document.getElementById('heroProgress')?.classList.remove('animating');
    });
    hero.addEventListener('mouseleave', startProgress);

    let tx = 0;
    hero.addEventListener('touchstart', e => {
        tx = e.touches[0].clientX;
    }, { passive: true });
    hero.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - tx;
        if (Math.abs(dx) > 50) dx < 0 ? nextSlide() : prevSlide();
    });
}

function goSlide(index) {
    const track = document.getElementById('slideTrack');
    const slides = document.querySelectorAll('.slide');
    if (!track || !slides.length) return;

    currentSlide = index;
    track.style.transform = `translateX(-${index * 100}%)`;
    slides.forEach((slide, i) => slide.classList.toggle('active', i === index));
    document.querySelectorAll('.hero-dot').forEach((dot, i) => dot.classList.toggle('active', i === index));

    const heroNum = document.getElementById('heroNum');
    if (heroNum) heroNum.textContent = String(index + 1).padStart(2, '0');
    startProgress();
}

function nextSlide() {
    const total = document.querySelectorAll('.slide').length;
    if (total) goSlide((currentSlide + 1) % total);
}

function prevSlide() {
    const total = document.querySelectorAll('.slide').length;
    if (total) goSlide((currentSlide - 1 + total) % total);
}

function startProgress() {
    const bar = document.getElementById('heroProgress');
    if (!bar) return;

    bar.classList.remove('animating');
    bar.style.width = '0%';
    clearTimeout(slideTimer);
    void bar.offsetWidth;
    bar.style.setProperty('--slide-dur', SLIDE_DUR + 'ms');
    bar.classList.add('animating');
    bar.style.width = '100%';
    slideTimer = setTimeout(nextSlide, SLIDE_DUR);
}

function initTrendTabs() {
    document.querySelectorAll('.trend-tab').forEach(tab => {
        tab.addEventListener('click', () => switchTrendTab(tab, tab.dataset.filter || 'all'));
    });
}

function switchTrendTab(el, filter) {
    document.querySelectorAll('.trend-tab').forEach(tab => tab.classList.remove('active'));
    el.classList.add('active');

    document.querySelectorAll('#trendList .tl-item').forEach(item => {
        const type = item.dataset.type || '';
        item.hidden = filter !== 'all' && type !== filter;
    });
}

function initScheduleTabs() {
    document.querySelectorAll('.day-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const day = tab.dataset.day;
            document.querySelectorAll('.day-tab').forEach(item => {
                const active = item === tab;
                item.classList.toggle('active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            document.querySelectorAll('#schedGrid .sched-day-panel').forEach(panel => {
                panel.hidden = panel.dataset.dayPanel !== day;
            });
        });
    });
}

const MEDIA_IMG_SKELETON_MAX_MS = 5000;

function initMediaImageSkeletons() {
    document.querySelectorAll('[data-media-img]').forEach(wrap => {
        const img = wrap.querySelector('img');
        if (!img) return;

        const done = () => wrap.classList.add('is-loaded');
        const forceDone = setTimeout(done, MEDIA_IMG_SKELETON_MAX_MS);

        const finish = () => {
            clearTimeout(forceDone);
            done();
        };

        if (img.complete && img.naturalWidth > 0) {
            finish();
            return;
        }

        img.addEventListener('load', finish, { once: true });
        img.addEventListener('error', () => {
            wrap.classList.add('is-error');
            finish();
        }, { once: true });
    });
}

function initLazySkeletons() {
    const cards = Array.from(document.querySelectorAll('.lazy-card.is-skeleton'));
    if (!cards.length) return;

    const reveal = card => {
        setTimeout(() => card.classList.remove('is-skeleton'), 180);
    };

    const revealVisible = () => {
        cards.forEach(card => {
            if (!card.classList.contains('is-skeleton')) return;
            const rect = card.getBoundingClientRect();
            const inRange = rect.top < window.innerHeight + 160 && rect.bottom > -160;
            if (inRange) reveal(card);
        });
    };

    revealVisible();
    window.addEventListener('scroll', revealVisible, { passive: true });
    window.addEventListener('resize', revealVisible);
}

function initNav() {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function (e) {
            document.querySelectorAll('.nav-link').forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            const key = this.dataset.nav;
            document.querySelectorAll('.bot-nav-item').forEach(item => {
                item.classList.toggle('active', item.dataset.nav === key);
            });
            scrollSamePageHash(e, this);
        });
    });

    document.querySelectorAll('.bot-nav-item').forEach(item => {
        item.addEventListener('click', function (e) {
            document.querySelectorAll('.bot-nav-item').forEach(navItem => navItem.classList.remove('active'));
            this.classList.add('active');
            const key = this.dataset.nav;
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.toggle('active', link.dataset.nav === key);
            });
            scrollSamePageHash(e, this);
        });
    });
}

function scrollSamePageHash(event, link) {
    const url = new URL(link.href, window.location.href);
    if (url.pathname !== window.location.pathname || !url.hash) return;
    const target = document.querySelector(url.hash);
    if (!target) return;

    event.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    history.replaceState(null, '', url.hash);
}

window.addEventListener('scroll', () => {
    document.getElementById('topnav')?.classList.toggle('scrolled', window.scrollY > 20);
}, { passive: true });

function scrollRow(id, dir) {
    document.getElementById(id)?.scrollBy({ left: dir * 580, behavior: 'smooth' });
}

let toastTimer;
function showToast(msg) {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 2200);
}

function initInterstitialAd() {
    const ad = document.getElementById('ad-interstitial');
    if (!ad) return;

    const storageKey = 'vexioInterstitialNextAt';
    const intervalMinutes = Math.max(1, Number(ad.dataset.intervalMinutes || 30));
    const dismissSeconds = Math.max(1, Number(ad.dataset.dismissSeconds || 5));
    const closeBtn = document.getElementById('interCloseBtn');
    const skipCount = document.getElementById('skipCount');
    const skipBtn = document.getElementById('skipBtn');
    const link = ad.querySelector('[data-interstitial-link]');
    let secondsLeft = dismissSeconds;
    let timer = null;

    function nextEligibleAt() {
        return Date.now() + intervalMinutes * 60 * 1000;
    }

    function rememberInterval() {
        try {
            window.localStorage.setItem(storageKey, String(nextEligibleAt()));
        } catch (error) {
            ad.dataset.intervalFallback = String(nextEligibleAt());
        }
    }

    function isEligible() {
        try {
            const stored = Number(window.localStorage.getItem(storageKey) || 0);
            return !stored || Date.now() >= stored;
        } catch (error) {
            const fallback = Number(ad.dataset.intervalFallback || 0);
            return !fallback || Date.now() >= fallback;
        }
    }

    function dismissInterstitial() {
        ad.classList.add('hidden');
        clearInterval(timer);
    }

    function showInterstitial() {
        rememberInterval();
        ad.classList.remove('hidden');
        if (skipCount) skipCount.textContent = String(secondsLeft);

        timer = setInterval(() => {
            secondsLeft -= 1;
            if (skipCount) skipCount.textContent = String(Math.max(0, secondsLeft));
            if (secondsLeft <= 0) dismissInterstitial();
        }, 1000);
    }

    closeBtn?.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        dismissInterstitial();
    });

    skipBtn?.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        dismissInterstitial();
    });

    link?.addEventListener('click', () => {
        rememberInterval();
        dismissInterstitial();
    });

    window.dismissInterstitial = dismissInterstitial;

    if (isEligible()) {
        showInterstitial();
    }
}

function initStickyMobileAd() {
    const ad = document.getElementById('ad-sticky-mobile');
    if (!ad) return;

    const close = ad.querySelector('.ad-sticky-close');
    close?.addEventListener('click', event => {
        event.preventDefault();
        ad.hidden = true;
    });
}
