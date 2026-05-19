<!-- ═══ AD-6 · INTERSTITIAL ENTRY AD ═════════════════ -->
<!-- Placement: Full-screen overlay on page load · Dismisses after 5s or on click -->
<div id="ad-interstitial">
  <a class="ad-inter-box" href="https://www.crunchyroll.com/premium" target="_blank" rel="noopener" onclick="dismissInterstitial()">
    <div class="ad-inter-img">
      <div class="ad-inter-label">Advertisement</div>
    </div>
    <div class="ad-inter-body">
      <div class="ad-inter-eyebrow">Sponsored · Crunchyroll Premium</div>
      <div class="ad-inter-headline">Watch Ad-Free.<br>Stream Unlimited.</div>
      <p class="ad-inter-sub">Get exclusive simulcasts, early access episodes, and zero ads on every show you love — starting at $7.99/mo.</p>
      <span class="ad-inter-skip" id="skipBtn">Skip in <span id="skipCount">5</span>s</span>
      <div class="ad-inter-timer" id="interTimerBar"></div>
    </div>
  </a>
  <button class="ad-inter-close-btn" id="interCloseBtn" onclick="dismissInterstitial()">×</button>
</div>
