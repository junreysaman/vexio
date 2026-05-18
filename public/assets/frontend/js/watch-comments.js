function initWatchComments() {
    document.addEventListener('submit', handleCommentSubmit);

    document.addEventListener('click', event => {
        const trigger = event.target.closest('.comment-reply-trigger');
        if (!trigger) return;

        const comment = trigger.closest('.comment');
        const system = trigger.closest('.comment-system');
        const slot = comment?.querySelector('.comment-reply-slot');
        const sourceForm = system?.querySelector('.comment-form');
        if (!comment || !system || !slot || !sourceForm) return;

        if (slot.querySelector('.comment-reply-form')) {
            slot.innerHTML = '';
            return;
        }

        system.querySelectorAll('.comment-reply-slot').forEach(item => {
            item.innerHTML = '';
        });

        slot.innerHTML = replyFormHtml(sourceForm, trigger.dataset.replyTo || '0', trigger.dataset.replyName || 'viewer');
    });
}

async function handleCommentSubmit(event) {
    const form = event.target.closest('.comment-form');
    if (!form) return;

    event.preventDefault();

    const textarea = form.querySelector('textarea[name="body"]');
    const submit = form.querySelector('.comment-submit');
    const body = textarea?.value.trim() || '';

    if (!body) {
        showToast('Write a comment first');
        return;
    }

    submit.disabled = true;
    const payload = new FormData(form);

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { Accept: 'application/json' },
            body: payload,
        });
        const data = await res.json();

        if (data.csrf_token || data?.error?.csrf_token) {
            document.querySelectorAll('input[name="token"]').forEach(input => {
                input.value = data.csrf_token || data.error.csrf_token;
            });
        }

        if (!res.ok) {
            throw new Error(data?.error?.message || 'Unable to post comment');
        }

        prependComment(form.closest('.comment-system'), data.comment);
        textarea.value = '';
        textarea.rows = 1;
        if (form.classList.contains('comment-reply-form')) form.remove();
        showToast(Number(data.comment?.parent_id || 0) > 0 ? 'Reply posted' : 'Comment posted');
    } catch (error) {
        showToast(error.message || 'Unable to post comment');
    } finally {
        submit.disabled = false;
    }
}

function replyFormHtml(sourceForm, parentId, name) {
    const token = sourceForm.querySelector('input[name="token"]')?.value || '';
    const ownerType = sourceForm.querySelector('input[name="owner_type"]')?.value || 'item';
    const ownerId = sourceForm.querySelector('input[name="owner_id"]')?.value || '0';

    return `
        <form class="comment-form comment-reply-form" action="/api/comments" method="POST">
            <input type="hidden" name="token" value="${escapeHtml(token)}">
            <input type="hidden" name="owner_type" value="${escapeHtml(ownerType)}">
            <input type="hidden" name="owner_id" value="${escapeHtml(ownerId)}">
            <input type="hidden" name="parent_id" value="${escapeHtml(parentId)}">
            <div class="comment-input-row">
                <div class="comment-compose">
                    <textarea class="ci-box" name="body" placeholder="Reply to ${escapeHtml(name)}..." rows="2" maxlength="1000"></textarea>
                    <div class="comment-compose-actions">
                        <span class="comment-count-label">Reply</span>
                        <button class="btn-secondary comment-submit" type="submit">Post Reply</button>
                    </div>
                </div>
            </div>
        </form>`;
}

function prependComment(system, comment) {
    const list = system?.querySelector('.comment-list');
    if (!list || !comment) return;

    list.querySelector('.comment-empty')?.remove();
    const name = comment.display_name || 'Guest Viewer';
    const initial = name.trim().charAt(0).toUpperCase() || 'V';
    const parentId = Number(comment.parent_id || 0);
    const node = commentNode(comment, name, initial, parentId > 0);

    if (parentId > 0) {
        const parent = system.querySelector(`.comment[data-comment-id="${parentId}"]`);
        const replies = parent?.querySelector('.comment-replies');
        if (replies) {
            replies.append(node);
        } else {
            list.prepend(node);
        }
    } else {
        list.prepend(node);
    }

    const label = system.querySelector('.comment-count-label');
    if (label) {
        const current = parseInt(label.textContent.replace(/[^\d]/g, ''), 10) || 0;
        label.textContent = `${(current + 1).toLocaleString()} comments`;
    }
}

function commentNode(comment, name, initial, isReply) {
    const node = document.createElement('div');
    node.className = `comment${isReply ? ' is-reply' : ''}`;
    node.dataset.commentId = String(comment.id || '');
    node.innerHTML = `
        <div class="c-avatar ca1">${escapeHtml(initial)}</div>
        <div class="c-body">
            <div class="c-header">
                <span class="c-name">${escapeHtml(name)}</span>
                <span class="c-time">Just now</span>
            </div>
            <div class="c-text">${escapeHtml(comment.body || '')}</div>
            <div class="c-actions">
                <span class="c-action" onclick="showToast('Thanks for the love')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    0
                </span>
                ${isReply ? '' : `<button class="c-action comment-reply-trigger" type="button" data-reply-to="${escapeHtml(comment.id || '')}" data-reply-name="${escapeHtml(name)}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Reply
                </button>`}
            </div>
            <div class="comment-reply-slot"></div>
            <div class="comment-replies"></div>
        </div>`;
    return node;
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWatchComments);
} else {
    initWatchComments();
}
