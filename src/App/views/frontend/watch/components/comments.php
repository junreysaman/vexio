<?php
$ownerType = (string) ($commentOwnerType ?? 'item');
$ownerId = (int) ($commentOwnerId ?? 0);
$comments = is_array($comments ?? null) ? $comments : [];
$commentCount = (int) ($commentCount ?? count($comments));
$commentPlaceholder = (string) ($commentPlaceholder ?? 'Share your thoughts...');
$canComment = !empty($currentUser);
$renderComment = function (array $comment, int $depth = 0) use (&$renderComment, $canComment): void {
  $name = (string) (($comment['display_name'] ?? '') ?: ($comment['username'] ?? 'Viewer'));
  $initial = strtoupper(substr($name, 0, 1));
  $created = strtotime((string) ($comment['created_at'] ?? '')) ?: time();
  $age = max(0, time() - $created);
  $timeLabel = $age < 60 ? 'Just now' : ($age < 3600 ? floor($age / 60) . ' min ago' : ($age < 86400 ? floor($age / 3600) . ' hours ago' : date('M j, Y', $created)));
  ?>
  <div class="comment <?= $depth > 0 ? 'is-reply' : '' ?>" data-comment-id="<?= (int) ($comment['id'] ?? 0) ?>">
    <div class="c-avatar ca<?= ((int) ($comment['id'] ?? 0) % 6) + 1 ?>"><?= escape($initial) ?></div>
    <div class="c-body">
      <div class="c-header">
        <span class="c-name"><?= escape($name) ?></span>
        <span class="c-time"><?= escape($timeLabel) ?></span>
      </div>
      <div class="c-text"><?= escape((string) ($comment['body'] ?? '')) ?></div>
      <div class="c-actions">
        <span class="c-action" onclick="showToast('Thanks for the love')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          <?= number_format((int) ($comment['likes'] ?? 0)) ?>
        </span>
        <?php if ($canComment && $depth < 2): ?>
          <button class="c-action comment-reply-trigger" type="button" data-reply-to="<?= (int) ($comment['id'] ?? 0) ?>" data-reply-name="<?= escape($name) ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            Reply
          </button>
        <?php endif; ?>
      </div>
      <div class="comment-reply-slot"></div>
      <div class="comment-replies">
        <?php foreach (($comment['replies'] ?? []) as $reply): ?>
          <?php $renderComment($reply, $depth + 1); ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php
};
?>
<div class="comment-system" data-comment-owner-type="<?= escape($ownerType) ?>" data-comment-owner-id="<?= $ownerId ?>">
  <?php if ($canComment): ?>
    <form class="comment-form" action="/api/comments" method="POST">
      <input type="hidden" name="token" value="<?= escape($_csrfToken ?? '') ?>">
      <input type="hidden" name="owner_type" value="<?= escape($ownerType) ?>">
      <input type="hidden" name="owner_id" value="<?= $ownerId ?>">
      <input type="hidden" name="parent_id" value="0">
      <div class="comment-input-row">
        <div class="ci-avatar"><?= escape(strtoupper(substr((string) (($currentUser['username'] ?? '') ?: 'V'), 0, 1))) ?></div>
        <div class="comment-compose">
          <textarea class="ci-box" name="body" placeholder="<?= escape($commentPlaceholder) ?>" rows="1" maxlength="1000" onfocus="this.rows=3"></textarea>
          <div class="comment-compose-actions">
            <span class="comment-count-label"><?= number_format($commentCount) ?> comments</span>
            <button class="btn-secondary comment-submit" type="submit">Post Comment</button>
          </div>
        </div>
      </div>
    </form>
  <?php else: ?>
    <div class="comment-login-prompt">
      <span class="comment-count-label"><?= number_format($commentCount) ?> comments</span>
      <a class="btn-secondary" href="/login">Sign In To Comment</a>
    </div>
  <?php endif; ?>

  <div class="comment-list">
    <?php foreach ($comments as $comment): ?>
      <?php $renderComment($comment); ?>
    <?php endforeach; ?>
    <?php if ($comments === []): ?>
      <div class="comment-empty">No comments yet. Be the first to start the conversation.</div>
    <?php endif; ?>
  </div>
</div>
