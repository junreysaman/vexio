<!-- Skeleton Loader for Production -->
<div id="skeletonLoader" class="skeleton-loader">
  <div class="skeleton-container">
    <!-- Navbar Skeleton -->
    <div class="skeleton-navbar">
      <div class="skeleton-logo skeleton-pulse"></div>
      <div class="skeleton-nav-links">
        <div class="skeleton-nav-item skeleton-pulse"></div>
        <div class="skeleton-nav-item skeleton-pulse"></div>
        <div class="skeleton-nav-item skeleton-pulse"></div>
        <div class="skeleton-nav-item skeleton-pulse"></div>
        <div class="skeleton-nav-item skeleton-pulse"></div>
      </div>
      <div class="skeleton-nav-right">
        <div class="skeleton-search skeleton-pulse"></div>
        <div class="skeleton-btn skeleton-pulse"></div>
      </div>
    </div>

    <!-- Main Content Skeleton -->
    <div class="skeleton-main">
      <!-- Hero Section -->
      <div class="skeleton-hero-section">
        <div class="skeleton-breadcrumb skeleton-pulse"></div>
        <div class="skeleton-hero-content">
          <div class="skeleton-hero-title skeleton-pulse"></div>
          <div class="skeleton-hero-subtitle skeleton-pulse"></div>
          <div class="skeleton-hero-meta skeleton-pulse"></div>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="skeleton-content-section">
        <div class="skeleton-section-header skeleton-pulse"></div>
        <div class="skeleton-grid">
          <?php for ($i = 0; $i < 8; $i++): ?>
            <div class="skeleton-card skeleton-pulse"></div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Second Content Section -->
      <div class="skeleton-content-section">
        <div class="skeleton-section-header skeleton-pulse"></div>
        <div class="skeleton-grid">
          <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="skeleton-card skeleton-pulse"></div>
          <?php endfor; ?>
        </div>
      </div>
    </div>

    <!-- Footer Skeleton -->
    <div class="skeleton-footer">
      <div class="skeleton-footer-content">
        <div class="skeleton-footer-col skeleton-pulse"></div>
        <div class="skeleton-footer-col skeleton-pulse"></div>
        <div class="skeleton-footer-col skeleton-pulse"></div>
        <div class="skeleton-footer-col skeleton-pulse"></div>
      </div>
      <div class="skeleton-footer-bottom skeleton-pulse"></div>
    </div>
  </div>
</div>

<style>
.skeleton-loader {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: var(--bg);
  overflow-y: auto;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.skeleton-loader.hidden {
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
}

.skeleton-container {
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Navbar Skeleton */
.skeleton-navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: var(--bg);
  border-bottom: 1px solid var(--border);
  padding: 16px 48px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

@media (max-width: 768px) {
  .skeleton-navbar {
    padding: 16px;
  }
}

.skeleton-logo {
  width: 120px;
  height: 32px;
  border-radius: 4px;
}

.skeleton-nav-links {
  display: flex;
  gap: 32px;
}

.skeleton-nav-item {
  width: 60px;
  height: 20px;
  border-radius: 4px;
}

.skeleton-nav-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.skeleton-search {
  width: 200px;
  height: 36px;
  border-radius: 8px;
}

.skeleton-btn {
  width: 80px;
  height: 36px;
  border-radius: 99px;
}

@media (max-width: 768px) {
  .skeleton-nav-links {
    display: none;
  }
  .skeleton-search {
    width: 120px;
  }
}

/* Main Content */
.skeleton-main {
  flex: 1;
  padding: 40px 48px;
}

@media (max-width: 768px) {
  .skeleton-main {
    padding: 24px 16px;
  }
}

/* Hero Section */
.skeleton-hero-section {
  margin-bottom: 48px;
}

.skeleton-breadcrumb {
  width: 300px;
  height: 20px;
  border-radius: 4px;
  margin-bottom: 24px;
}

.skeleton-hero-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.skeleton-hero-title {
  width: 60%;
  height: 48px;
  border-radius: 8px;
}

.skeleton-hero-subtitle {
  width: 80%;
  height: 24px;
  border-radius: 6px;
}

.skeleton-hero-meta {
  width: 40%;
  height: 20px;
  border-radius: 4px;
}

/* Content Section */
.skeleton-content-section {
  margin-bottom: 48px;
}

.skeleton-section-header {
  width: 200px;
  height: 32px;
  border-radius: 6px;
  margin-bottom: 24px;
}

.skeleton-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

@media (max-width: 1200px) {
  .skeleton-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 900px) {
  .skeleton-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .skeleton-grid {
    grid-template-columns: 1fr;
  }
}

.skeleton-card {
  aspect-ratio: 2/3;
  border-radius: 12px;
}

/* Footer Skeleton */
.skeleton-footer {
  background: var(--bg2);
  border-top: 1px solid var(--border);
  padding: 48px;
}

.skeleton-footer-content {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 32px;
  margin-bottom: 32px;
}

@media (max-width: 900px) {
  .skeleton-footer-content {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .skeleton-footer-content {
    grid-template-columns: 1fr;
  }
}

.skeleton-footer-col {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-footer-col > div {
  height: 16px;
  border-radius: 4px;
}

.skeleton-footer-col > div:first-child {
  width: 60%;
  height: 24px;
  margin-bottom: 8px;
}

.skeleton-footer-col > div:nth-child(2) {
  width: 80%;
}

.skeleton-footer-col > div:nth-child(3) {
  width: 70%;
}

.skeleton-footer-col > div:nth-child(4) {
  width: 75%;
}

.skeleton-footer-bottom {
  height: 40px;
  border-radius: 4px;
  border-top: 1px solid var(--border);
  padding-top: 24px;
}

/* Pulse Animation */
@keyframes skeletonPulse {
  0%, 100% {
    opacity: 0.4;
  }
  50% {
    opacity: 0.7;
  }
}

.skeleton-pulse {
  background: linear-gradient(
    90deg,
    var(--bg3) 0%,
    var(--bg2) 50%,
    var(--bg3) 100%
  );
  background-size: 200% 100%;
  animation: skeletonPulse 1.5s ease-in-out infinite,
             shimmer 1.5s ease-in-out infinite;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style>

<script>
// Hide skeleton loader when page is fully loaded
window.addEventListener('load', () => {
  const skeletonLoader = document.getElementById('skeletonLoader');
  if (skeletonLoader) {
    // Small delay to ensure smooth transition
    setTimeout(() => {
      skeletonLoader.classList.add('hidden');
    }, 300);
  }
});

// Fallback: hide after 3 seconds even if load event doesn't fire
setTimeout(() => {
  const skeletonLoader = document.getElementById('skeletonLoader');
  if (skeletonLoader && !skeletonLoader.classList.contains('hidden')) {
    skeletonLoader.classList.add('hidden');
  }
}, 3000);
</script>
