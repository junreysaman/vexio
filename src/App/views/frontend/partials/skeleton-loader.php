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
