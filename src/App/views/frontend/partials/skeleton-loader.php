<!-- Full-page skeleton shown until the document is ready (min 5s) -->
<div id="skeletonLoader" class="skeleton-loader" aria-hidden="true">
  <div class="skeleton-container">
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

    <div class="skeleton-main">
      <div class="skeleton-hero-section">
        <div class="skeleton-breadcrumb skeleton-pulse"></div>
        <div class="skeleton-hero-content">
          <div class="skeleton-hero-title skeleton-pulse"></div>
          <div class="skeleton-hero-subtitle skeleton-pulse"></div>
          <div class="skeleton-hero-meta skeleton-pulse"></div>
        </div>
      </div>

      <div class="skeleton-content-section">
        <div class="skeleton-section-header skeleton-pulse"></div>
        <div class="skeleton-grid">
          <?php for ($i = 0; $i < 8; $i++): ?>
            <div class="skeleton-card skeleton-pulse"></div>
          <?php endfor; ?>
        </div>
      </div>

      <div class="skeleton-content-section">
        <div class="skeleton-section-header skeleton-pulse"></div>
        <div class="skeleton-grid">
          <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="skeleton-card skeleton-pulse"></div>
          <?php endfor; ?>
        </div>
      </div>
    </div>

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
