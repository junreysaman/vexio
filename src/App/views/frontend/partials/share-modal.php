<?php
$pageUrl = $pageUrl ?? ($_SERVER['REQUEST_URI'] ?? '/');
$pageTitle = $pageTitle ?? ($title ?? 'Check this out');
$pageImage = $pageImage ?? ($meta_image ?? '/favicon.png');
?>
<div id="shareModal" class="share-modal-overlay" hidden>
  <div class="share-modal">
    <div class="share-modal-header">
      <h3>Share</h3>
      <button class="share-modal-close" onclick="closeShareModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>
    <div class="share-modal-body">
      <div class="share-platforms">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url($pageUrl)) ?>" target="_blank" class="share-platform facebook" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
          <span>Facebook</span>
        </a>
        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($pageTitle) ?>&url=<?= urlencode(url($pageUrl)) ?>" target="_blank" class="share-platform twitter" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
          <span>Twitter</span>
        </a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(url($pageUrl)) ?>" target="_blank" class="share-platform linkedin" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
          <span>LinkedIn</span>
        </a>
        <a href="https://pinterest.com/pin/create/button/?url=<?= urlencode(url($pageUrl)) ?>&media=<?= urlencode(url($pageImage)) ?>&description=<?= urlencode($pageTitle) ?>" target="_blank" class="share-platform pinterest" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-4 19.2 10 10 0 0 0 1.8-5.6 3 3 0 0 1 2.2-2.2 3 3 0 0 1 3.4 3.4 3 3 0 0 1-1.2 2.4 3 3 0 0 0-1.2 2.4v.6a3 3 0 0 0 3 3 3 3 0 0 0 3-3v-.6a6 6 0 0 0-6-6 6 6 0 0 0-6 6v.6a6 6 0 0 0 6 6 6 6 0 0 0 6-6v-.6a3 3 0 0 1-1.2-2.4 3 3 0 0 1 3.4-3.4 3 3 0 0 1 2.2 2.2 3 3 0 0 1-1.8 5.6A10 10 0 0 0 12 2z"></path></svg>
          <span>Pinterest</span>
        </a>
        <a href="https://wa.me/?text=<?= urlencode($pageTitle . ' ' . url($pageUrl)) ?>" target="_blank" class="share-platform whatsapp" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"></path></svg>
          <span>WhatsApp</span>
        </a>
        <a href="https://t.me/share/url?url=<?= urlencode(url($pageUrl)) ?>&text=<?= urlencode($pageTitle) ?>" target="_blank" class="share-platform telegram" rel="noopener noreferrer">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 2L2 9l9 5 5 9 13-21z"></path><path d="M11 14l5 5"></path></svg>
          <span>Telegram</span>
        </a>
        <a href="mailto:?subject=<?= urlencode($pageTitle) ?>&body=<?= urlencode($pageTitle . ' ' . url($pageUrl)) ?>" class="share-platform email">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
          <span>Email</span>
        </a>
      </div>
      <div class="share-copy-link">
        <input type="text" id="shareUrlInput" value="<?= escape(url($pageUrl)) ?>" readonly>
        <button class="share-copy-btn" onclick="copyShareUrl()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
          Copy
        </button>
      </div>
    </div>
  </div>
</div>

<style>
.share-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 16px;
}

.share-modal {
  background: var(--bg2);
  border: 1px solid var(--border);
  border-radius: 16px;
  max-width: 420px;
  width: 100%;
  overflow: hidden;
  animation: shareModalSlideIn 0.3s ease;
}

@keyframes shareModalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.share-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid var(--border);
}

.share-modal-header h3 {
  font-size: 18px;
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

.share-modal-close {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--bg3);
  color: var(--muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.share-modal-close:hover {
  border-color: var(--purple);
  color: var(--purple);
}

.share-modal-close svg {
  width: 16px;
  height: 16px;
}

.share-modal-body {
  padding: 24px;
}

.share-platforms {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

.share-platform {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--bg3);
  color: var(--text);
  text-decoration: none;
  transition: all 0.2s;
}

.share-platform:hover {
  border-color: var(--purple);
  background: var(--bg2);
}

.share-platform svg {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.share-platform.facebook svg { color: #1877f2; }
.share-platform.twitter svg { color: #1da1f2; }
.share-platform.linkedin svg { color: #0a66c2; }
.share-platform.pinterest svg { color: #e60023; }
.share-platform.whatsapp svg { color: #25d366; }
.share-platform.telegram svg { color: #0088cc; }
.share-platform.email svg { color: var(--muted); }

.share-platform span {
  font-size: 14px;
  font-weight: 600;
}

.share-copy-link {
  display: flex;
  gap: 8px;
}

.share-copy-link input {
  flex: 1;
  background: var(--bg3);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  color: var(--text);
  outline: none;
}

.share-copy-link input:focus {
  border-color: var(--purple);
}

.share-copy-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 16px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--bg3);
  color: var(--text);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.share-copy-btn:hover {
  border-color: var(--purple);
  background: var(--bg2);
}

.share-copy-btn svg {
  width: 14px;
  height: 14px;
}

@media (max-width: 480px) {
  .share-platforms {
    grid-template-columns: 1fr;
  }
}
</style>

<script>
function openShareModal(url, title, image) {
  const modal = document.getElementById('shareModal');
  const input = document.getElementById('shareUrlInput');
  
  if (url) input.value = url;
  modal.hidden = false;
  document.body.style.overflow = 'hidden';
}

function closeShareModal() {
  const modal = document.getElementById('shareModal');
  modal.hidden = true;
  document.body.style.overflow = '';
}

function copyShareUrl() {
  const input = document.getElementById('shareUrlInput');
  input.select();
  navigator.clipboard.writeText(input.value).then(() => {
    const btn = document.querySelector('.share-copy-btn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Copied!';
    setTimeout(() => {
      btn.innerHTML = originalText;
    }, 2000);
  });
}

// Close modal on overlay click
document.getElementById('shareModal')?.addEventListener('click', (e) => {
  if (e.target.id === 'shareModal') {
    closeShareModal();
  }
});

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeShareModal();
  }
});
</script>
