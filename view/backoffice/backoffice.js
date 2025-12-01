/**
 * EcoTrack Backoffice Administration Panel
 * 
 * This script manages the backoffice interface for moderating posts and comments.
 * Features include:
 * - Dynamic loading and rendering of posts with comments
 * - Interactive dashboard with animated counters
 * - Modal-based editing and deletion workflows
 * - Real-time updates and user feedback
 * - Accessibility-compliant UI elements
 * 
 * @version 2.1
 * @author EcoTrack Team
 */

'use strict';

// ============================================================================
// STATE MANAGEMENT
// ============================================================================

let posts = [];

/**
 * Dashboard statistics counters
 * Track user actions for overview display
 */
let sentCount = 0;       // Total posts sent
let commentCount = 0;    // Total comments across all posts
let editCount = 0;       // Number of edits made
let deleteCount = 0;     // Number of deletions made
let modifiedItems = [];  // Track details of modified items for display

// ============================================================================
// DASHBOARD UPDATE FUNCTIONS
// ============================================================================

/**
 * Updates all dashboard counter displays with smooth animations
 * Syncs both sidebar counters and overview card statistics
 */

function updateDashboard() {
    const sentEl = document.getElementById('sentCount');
    const commentEl = document.getElementById('commentCount');
    const editEl = document.getElementById('editCount');
    const delEl = document.getElementById('deleteCount');
    // animate numeric counters (if present) for a nicer UX
    const ovSent = document.getElementById('overviewSent');
    const ovComments = document.getElementById('overviewComments');
    const ovEdit = document.getElementById('overviewEdit');
    const ovDelete = document.getElementById('overviewDelete');

    // Helper: animate an element's integer text from current to target
    function animateCount(el, to, duration = 700) {
        if (!el) return;
        const raw = String(el.textContent || el.dataset.value || '0').replace(/[^0-9\-]+/g, '');
        const from = parseInt(raw, 10) || 0;
        const start = performance.now();
        const diff = to - from;
        if (diff === 0) { el.textContent = String(to); el.dataset.value = to; return; }
        function easeInOut(t) { return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t; }
        function step(now) {
            const t = Math.min(1, (now - start) / duration);
            const v = Math.round(from + diff * easeInOut(t));
            el.textContent = String(v);
            if (t < 1) requestAnimationFrame(step);
            else el.dataset.value = String(to);
        }
        requestAnimationFrame(step);
    }

    // animate sidebar counters and overview cards
    if (sentEl) animateCount(sentEl, sentCount);
    if (commentEl) animateCount(commentEl, commentCount);
    if (editEl) animateCount(editEl, editCount);
    if (delEl) animateCount(delEl, deleteCount);

    if (ovSent) animateCount(ovSent, sentCount);
    if (ovComments) animateCount(ovComments, commentCount);
    if (ovEdit) animateCount(ovEdit, editCount);
    if (ovDelete) animateCount(ovDelete, deleteCount);

    // Add a small pulse effect to overview numbers when updated
    [ovSent, ovComments, ovEdit, ovDelete].forEach(el => {
        if (!el) return;
        el.classList.remove('pulse');
        // trigger reflow to restart animation
        void el.offsetWidth;
        el.classList.add('pulse');
        setTimeout(() => el.classList.remove('pulse'), 900);
    });
}

// ============================================================================
// USER FEEDBACK FUNCTIONS
// ============================================================================

/**
 * Displays a status message to the user in the backoffice status element
 * Falls back to console logging if status element is not found
 * 
 * @param {string} msg - The message to display
 * @param {boolean} isError - Whether this is an error message (affects styling)
 * @param {number} timeout - Auto-clear timeout in milliseconds (0 = no auto-clear)
 */
function showBackofficeMessage(msg, isError = false, timeout = 6000) {
    const statusEl = document.getElementById('backofficeStatus');
    if (statusEl) {
        statusEl.textContent = msg;
        statusEl.style.color = isError ? '#a33' : '#2b3b36';
        if (timeout > 0) {
            setTimeout(() => {
                // only clear if unchanged
                if (statusEl.textContent === msg) statusEl.textContent = '';
            }, timeout);
        }
    } else {
        // fallback to console when no status element
        if (isError) console.error(msg); else console.log(msg);
    }
}

function parseJsonSafe(response) {
    const ct = response.headers.get('content-type') || '';
    // Try to parse JSON; if content-type isn't JSON or parsing fails, return raw text on the error
    if (!ct.includes('application/json')) {
        return response.text().then(text => {
            const err = new Error('Invalid JSON response');
            err.raw = text;
            throw err;
        });
    }
    // content-type says json; try parsing but capture raw body on parse failure
    return response.text().then(text => {
        try {
            return JSON.parse(text);
        } catch (ex) {
            const err = new Error('JSON parse error');
            err.raw = text;
            throw err;
        }
    });
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text).replace(/[&<>"']/g, function (s) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[s]);
    });
}

function renderPosts(postsToRender = null) {
    // If no specific array passed, use the global posts array
    const currentPosts = postsToRender || posts;

    const backofficeFeed = document.getElementById('backofficeFeed');
    if (!backofficeFeed) return;
    // clear and add a strong heading + toolbar + debug area
    backofficeFeed.innerHTML = '<h2 style="margin-top:0;">Gestion des publications et commentaires</h2>' +
        '<div class="feed-toolbar" style="margin:12px 0; display:flex; gap:8px; align-items:center;">' +
        '<button id="refreshFeed" class="btn-feed-refresh"><span class="refresh-icon">🔄</span> Rafraîchir</button>' +
        '</div>' +
        '<div id="backofficeStatus" style="margin-bottom:8px;color:#2b3b36;font-weight:600"></div>';

    if (!Array.isArray(currentPosts) || currentPosts.length === 0) {
        backofficeFeed.innerHTML += '<div style="color:#666">Aucune publication trouvée.</div>';
        // Only update global stats if we are rendering the full list (not filtered)
        if (!postsToRender) {
            sentCount = 0;
            commentCount = 0;
            updateDashboard();
        }
        return;
    }

    let totalComments = 0;
    // Render each post as a clear card
    currentPosts.forEach(post => {
        const postEl = document.createElement('div');
        postEl.style.background = '#ffffff';
        postEl.style.border = '1px solid #e6e6e6';
        postEl.style.padding = '14px';
        postEl.style.marginBottom = '12px';
        postEl.style.borderRadius = '8px';
        postEl.classList.add('publication');
        postEl.dataset.postId = post.id;

        const author = escapeHtml(post.send_by || 'Anonyme');
        const body = escapeHtml(post.contenu || '');
        const time = escapeHtml(post.time || '');
        const commentCount = (post.comments && Array.isArray(post.comments)) ? post.comments.length : 0;
        totalComments += commentCount;

        postEl.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <div style="flex:1">
                    <div style="font-weight:700;color:#2b3b36;margin-bottom:6px;">${author}</div>
                    <div class="post-text" style="color:#222;">${body}</div>
                    <div class="pub-date" style="color:#666;margin-top:8px;font-size:0.9em;">${time} • ${commentCount} commentaire(s)</div>
                </div>
                <div style="flex:0 0 auto;display:flex;flex-direction:column;gap:8px;margin-left:12px;">
                    <button class="view-post-btn" data-post-id="${post.id}" style="background:#357a38;color:#fff;border:none;padding:8px 10px;border-radius:6px;">Voir</button>
                    <button class="add-comment-btn" data-post-id="${post.id}" style="background:#60c072;color:#fff;border:none;padding:8px 10px;border-radius:6px;">💬 Commenter</button>
                    <button class="edit-post-btn" data-post-id="${post.id}" style="background:#2b6f2e;color:#fff;border:none;padding:8px 10px;border-radius:6px;">Modifier</button>
                    <button class="delete-post-btn" data-post-id="${post.id}" style="background:#D9534F;color:#fff;border:none;padding:8px 10px;border-radius:6px;">Supprimer</button>
                </div>
            </div>
            <div class="comments-section" data-post-id="${post.id}" style="display:none;margin-top:16px;padding-top:16px;border-top:1px solid #e6e6e6;">
                <h4 style="margin:0 0 12px 0;color:#2b3b36;">Commentaires (${commentCount})</h4>
                <div class="comments-list"></div>
            </div>
        `;
        backofficeFeed.appendChild(postEl);
    });

    // Only update global dashboard stats if not filtering
    if (!postsToRender) {
        sentCount = currentPosts.length;
        commentCount = totalComments;
        updateDashboard();
    }

    // update status
    const status = document.getElementById('backofficeStatus');
    if (status) {
        status.textContent = `Publications affichées: ${currentPosts.length}`;
    }
}

// ============================================================================
// MODAL MANAGEMENT
// ============================================================================

/**
 * Creates and initializes the comment/post editing modal if it doesn't exist
 * Sets up event handlers for save, delete, and close actions
 */
function ensureModal() {
    if (document.getElementById('commentModal')) return;
    const modal = document.createElement('div');
    modal.id = 'commentModal';
    modal.style.position = 'fixed';
    modal.style.left = '0';
    modal.style.top = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.display = 'none';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.background = 'rgba(0,0,0,0.45)';
    modal.innerHTML = `<div id="commentModalContent" style="background:#fff;padding:18px;border-radius:8px;max-width:720px;width:90%;box-shadow:0 8px 30px rgba(0,0,0,0.2);">
        <button id="closeModal" style="float:right;background:transparent;border:none;font-size:18px;">✕</button>
        <div style="margin-bottom:8px;"><label for="modalAuthor" style="display:block;font-weight:600;margin-bottom:6px;">Auteur</label><input id="modalAuthor" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;"/></div>
        <div id="modalTime" style="color:#666;margin-bottom:12px"></div>
        <div style="margin-bottom:8px;"><label for="modalContent" style="display:block;font-weight:600;margin-bottom:6px;">Contenu</label><textarea id="modalContent" style="width:100%;min-height:120px;padding:8px;border:1px solid #ddd;border-radius:6px;"></textarea></div>
        <div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end;"><button id="modalSave" class="btn-primary">Enregistrer</button><button id="modalDelete" class="btn-danger">Supprimer</button></div>
    </div>`;
    document.body.appendChild(modal);
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('modalSave').addEventListener('click', function () {
        const type = this.getAttribute('data-type');
        const contenu = document.getElementById('modalContent').value;
        const send_by = document.getElementById('modalAuthor').value || '';

        if (type === 'post') {
            const postId = this.getAttribute('data-post-id');
            fetch('../../controller/communityController.php', {
                method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&contenu=${encodeURIComponent(contenu)}&send_by=${encodeURIComponent(send_by)}`
            }).then(parseJsonSafe).then(data => {
                if (data && data.success) {
                    closeModal();
                    fetchPosts();
                    // Track modification
                    editCount++;
                    modifiedItems.push({
                        type: 'Publication',
                        id: postId,
                        author: send_by,
                        content: contenu,
                        time: new Date().toLocaleTimeString()
                    });
                    updateDashboard();
                } else { showBackofficeMessage('Erreur: ' + (data && data.error ? data.error : 'Réponse invalide'), true); }
            }).catch(err => { showBackofficeMessage('Erreur lors de la sauvegarde. Voir console. ' + (err.raw || err.message || ''), true); console.error(err); });
        } else if (type === 'comment') {
            const commentId = this.getAttribute('data-comment-id');
            fetch('../../controller/communityController.php', {
                method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${commentId}&contenu=${encodeURIComponent(contenu)}&send_by=${encodeURIComponent(send_by)}`
            }).then(parseJsonSafe).then(data => {
                if (data && data.success) {
                    closeModal();
                    fetchPosts();
                    // Track modification
                    editCount++;
                    modifiedItems.push({
                        type: 'Commentaire',
                        id: commentId,
                        author: send_by,
                        content: contenu,
                        time: new Date().toLocaleTimeString()
                    });
                    updateDashboard();
                } else { showBackofficeMessage('Erreur: ' + (data && data.error ? data.error : 'Réponse invalide'), true); }
            }).catch(err => { showBackofficeMessage('Erreur lors de la sauvegarde. Voir console. ' + (err.raw || err.message || ''), true); console.error(err); });
        } else if (type === 'add-comment') {
            // Add new comment to post
            const postId = this.getAttribute('data-post-id');
            if (!contenu.trim()) {
                showBackofficeMessage('Le commentaire ne peut pas être vide', true);
                return;
            }
            fetch('../../controller/communityController.php', {
                method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `parent_id=${postId}&contenu=${encodeURIComponent(contenu)}&send_by=${encodeURIComponent(send_by)}`
            }).then(parseJsonSafe).then(data => {
                if (data && data.success) {
                    commentCount++;
                    updateDashboard();
                    showBackofficeMessage('Commentaire ajouté avec succès!', false);
                    closeModal();
                    fetchPosts();
                } else {
                    showBackofficeMessage('Erreur: ' + (data && data.error ? data.error : 'Réponse invalide'), true);
                }
            }).catch(err => { showBackofficeMessage('Erreur lors de l\'ajout du commentaire. Voir console. ' + (err.raw || err.message || ''), true); console.error(err); });
        }
    });
    document.getElementById('modalDelete').addEventListener('click', function () {
        const type = this.getAttribute('data-type');
        const btn = this;
        btn.disabled = true;
        const prev = btn.textContent;
        btn.textContent = 'Suppression...';

        if (type === 'post') {
            const postId = this.getAttribute('data-post-id');
            fetch('../../controller/communityController.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `post_id=${postId}` })
                .then(parseJsonSafe).then(data => { if (data && data.success) { closeModal(); fetchPosts(); } else { showBackofficeMessage('Erreur suppression: ' + (data && data.error ? data.error : 'Réponse invalide'), true); } })
                .catch(err => { showBackofficeMessage('Erreur lors de la suppression. Voir console. ' + (err.raw || err.message || ''), true); console.error(err); })
                .finally(() => { btn.disabled = false; btn.textContent = prev; });
        } else if (type === 'comment') {
            const commentId = this.getAttribute('data-comment-id');
            fetch('../../controller/communityController.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `id=${commentId}` })
                .then(parseJsonSafe).then(data => { if (data && data.success) { closeModal(); fetchPosts(); } else { showBackofficeMessage('Erreur suppression: ' + (data && data.error ? data.error : 'Réponse invalide'), true); } })
                .catch(err => { showBackofficeMessage('Erreur lors de la suppression. Voir console. ' + (err.raw || err.message || ''), true); console.error(err); })
                .finally(() => { btn.disabled = false; btn.textContent = prev; });
        }
    });
}

function showModalForPost(postId) {
    ensureModal();
    const modal = document.getElementById('commentModal');
    const modalAuthor = document.getElementById('modalAuthor');
    const modalTime = document.getElementById('modalTime');
    const modalContent = document.getElementById('modalContent');
    const modalSave = document.getElementById('modalSave');
    const modalDelete = document.getElementById('modalDelete');
    const post = posts.find(x => String(x.id) === String(postId));
    if (!post) { showBackofficeMessage('Publication introuvable', true); return; }
    modalAuthor.value = post.send_by || 'Anonyme';
    modalTime.textContent = post.time || '';
    modalContent.value = post.contenu || '';
    modalSave.setAttribute('data-post-id', post.id);
    modalSave.setAttribute('data-type', 'post');
    modalDelete.setAttribute('data-post-id', post.id);
    modalDelete.setAttribute('data-type', 'post');

    // Apply green styling to save button
    modalSave.innerHTML = '✏️ Enregistrer';
    modalSave.classList.add('btn-admin-comment');
    modalSave.style.background = 'linear-gradient(135deg, #60c072 0%, #357a38 100%)';
    modalSave.style.padding = '12px 24px';
    modalSave.style.fontSize = '15px';
    modalSave.style.fontWeight = '800';
    modalSave.style.boxShadow = '0 8px 24px rgba(47, 155, 74, 0.35)';
    modalSave.style.transition = 'all 0.3s ease';
    modalSave.style.border = 'none';
    modalSave.style.borderRadius = '8px';
    modalSave.style.color = 'white';
    modalSave.style.cursor = 'pointer';
    modalSave.style.textTransform = 'uppercase';
    modalSave.style.letterSpacing = '0.5px';

    // Apply red styling to delete button
    modalDelete.style.display = 'inline-block';
    modalDelete.classList.add('btn-admin-delete');
    modalDelete.innerHTML = '🗑️ Supprimer';
    modalDelete.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #c92a2a 100%)';
    modalDelete.style.padding = '12px 24px';
    modalDelete.style.fontSize = '15px';
    modalDelete.style.fontWeight = '800';
    modalDelete.style.boxShadow = '0 8px 24px rgba(201, 42, 42, 0.35)';
    modalDelete.style.transition = 'all 0.3s ease';
    modalDelete.style.border = 'none';
    modalDelete.style.borderRadius = '8px';
    modalDelete.style.color = 'white';
    modalDelete.style.cursor = 'pointer';
    modalDelete.style.textTransform = 'uppercase';
    modalDelete.style.letterSpacing = '0.5px';
    modalDelete.disabled = false;
    modalDelete.style.opacity = '1';
    modalDelete.style.filter = '';

    modal.style.display = 'flex';
}

function showModalForComment(commentId, postId) {
    ensureModal();
    const modal = document.getElementById('commentModal');
    const modalAuthor = document.getElementById('modalAuthor');
    const modalTime = document.getElementById('modalTime');
    const modalContent = document.getElementById('modalContent');
    const modalSave = document.getElementById('modalSave');
    const modalDelete = document.getElementById('modalDelete');
    const post = posts.find(x => String(x.id) === String(postId));
    if (!post) { showBackofficeMessage('Publication introuvable', true); return; }
    const c = post.comments.find(x => String(x.id) === String(commentId));
    if (!c) { showBackofficeMessage('Commentaire introuvable', true); return; }
    modalAuthor.value = c.send_by || 'Anonyme';
    modalTime.textContent = '';
    modalContent.value = c.contenu || '';
    modalSave.setAttribute('data-comment-id', c.id);
    modalSave.setAttribute('data-type', 'comment');
    modalDelete.setAttribute('data-comment-id', c.id);
    modalDelete.setAttribute('data-type', 'comment');

    // Apply green styling to save button
    modalSave.innerHTML = '✏️ Enregistrer';
    modalSave.classList.add('btn-admin-comment');
    modalSave.style.background = 'linear-gradient(135deg, #60c072 0%, #357a38 100%)';
    modalSave.style.padding = '12px 24px';
    modalSave.style.fontSize = '15px';
    modalSave.style.fontWeight = '800';
    modalSave.style.boxShadow = '0 8px 24px rgba(47, 155, 74, 0.35)';
    modalSave.style.transition = 'all 0.3s ease';
    modalSave.style.border = 'none';
    modalSave.style.borderRadius = '8px';
    modalSave.style.color = 'white';
    modalSave.style.cursor = 'pointer';
    modalSave.style.textTransform = 'uppercase';
    modalSave.style.letterSpacing = '0.5px';

    // Apply red styling to delete button
    modalDelete.style.display = 'inline-block';
    modalDelete.classList.add('btn-admin-delete');
    modalDelete.innerHTML = '🗑️ Supprimer';
    modalDelete.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #c92a2a 100%)';
    modalDelete.style.padding = '12px 24px';
    modalDelete.style.fontSize = '15px';
    modalDelete.style.fontWeight = '800';
    modalDelete.style.boxShadow = '0 8px 24px rgba(201, 42, 42, 0.35)';
    modalDelete.style.transition = 'all 0.3s ease';
    modalDelete.style.border = 'none';
    modalDelete.style.borderRadius = '8px';
    modalDelete.style.color = 'white';
    modalDelete.style.cursor = 'pointer';
    modalDelete.style.textTransform = 'uppercase';
    modalDelete.style.letterSpacing = '0.5px';
    modalDelete.disabled = false;
    modalDelete.style.opacity = '1';
    modalDelete.style.filter = '';

    modal.style.display = 'flex';
}

function toggleCommentsForPost(postId) {
    const commentsSection = document.querySelector(`.comments-section[data-post-id="${postId}"]`);
    if (!commentsSection) return;

    const isVisible = commentsSection.style.display !== 'none';
    if (isVisible) {
        commentsSection.style.display = 'none';
    } else {
        // Render comments
        const post = posts.find(x => String(x.id) === String(postId));
        if (!post) return;

        const commentsList = commentsSection.querySelector('.comments-list');
        commentsList.innerHTML = '';

        if (!post.comments || post.comments.length === 0) {
            commentsList.innerHTML = '<div style="color:#666;font-style:italic;">Aucun commentaire</div>';
        } else {
            post.comments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.style.background = '#f9f9f9';
                commentDiv.style.padding = '10px';
                commentDiv.style.marginBottom = '8px';
                commentDiv.style.borderRadius = '6px';
                commentDiv.style.border = '1px solid #e0e0e0';

                const author = escapeHtml(comment.send_by || 'Anonyme');
                const body = escapeHtml(comment.contenu || '');

                commentDiv.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;">
                        <div style="flex:1">
                            <div style="font-weight:600;color:#2b3b36;margin-bottom:4px;">${author}</div>
                            <div style="color:#333;">${body}</div>
                        </div>
                        <div style="flex:0 0 auto;display:flex;gap:6px;">
                            <button class="edit-comment-btn" data-comment-id="${comment.id}" data-post-id="${postId}" style="background:#2b6f2e;color:#fff;border:none;padding:6px 8px;border-radius:4px;font-size:0.85em;">Modifier</button>
                            <button class="delete-comment-btn" data-comment-id="${comment.id}" style="background:#D9534F;color:#fff;border:none;padding:6px 8px;border-radius:4px;font-size:0.85em;">Supprimer</button>
                        </div>
                    </div>
                `;
                commentsList.appendChild(commentDiv);
            });
        }

        commentsSection.style.display = 'block';
    }
}

/**
 * Shows a modal to add a new comment to a post
 * @param {string|number} postId - The ID of the post to add a comment to
 */
function showAddCommentModal(postId) {
    ensureModal();
    const modal = document.getElementById('commentModal');
    const modalAuthor = document.getElementById('modalAuthor');
    const modalTime = document.getElementById('modalTime');
    const modalContent = document.getElementById('modalContent');
    const modalSave = document.getElementById('modalSave');
    const modalDelete = document.getElementById('modalDelete');

    const post = posts.find(x => String(x.id) === String(postId));
    if (!post) { showBackofficeMessage('Publication introuvable', true); return; }

    // Set up modal for adding a new comment
    modalAuthor.value = 'Admin EcoTrack'; // Default admin username
    modalTime.textContent = 'Nouveau commentaire';
    modalContent.value = '';
    modalContent.placeholder = 'Entrez votre commentaire ici...';

    // Configure save button for adding comment with special admin styling
    modalSave.setAttribute('data-post-id', postId);
    modalSave.setAttribute('data-type', 'add-comment');
    modalSave.innerHTML = '💬 Ajouter le commentaire'; // Add icon
    modalSave.classList.add('btn-admin-comment'); // Special CSS class

    // Apply special inline styles for admin comment button
    modalSave.style.background = 'linear-gradient(135deg, #60c072 0%, #357a38 100%)';
    modalSave.style.padding = '12px 24px';
    modalSave.style.fontSize = '15px';
    modalSave.style.fontWeight = '800';
    modalSave.style.boxShadow = '0 8px 24px rgba(47, 155, 74, 0.35)';
    modalSave.style.transition = 'all 0.3s ease';
    modalSave.style.border = 'none';
    modalSave.style.borderRadius = '8px';
    modalSave.style.color = 'white';
    modalSave.style.cursor = 'pointer';
    modalSave.style.textTransform = 'uppercase';
    modalSave.style.letterSpacing = '0.5px';

    // Style delete button with red admin design
    modalDelete.style.display = 'inline-block';
    modalDelete.classList.add('btn-admin-delete'); // Special CSS class
    modalDelete.innerHTML = '🗑️ Supprimer'; // Add icon

    // Apply special inline styles for admin delete button
    modalDelete.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #c92a2a 100%)';
    modalDelete.style.padding = '12px 24px';
    modalDelete.style.fontSize = '15px';
    modalDelete.style.fontWeight = '800';
    modalDelete.style.boxShadow = '0 8px 24px rgba(201, 42, 42, 0.35)';
    modalDelete.style.transition = 'all 0.3s ease';
    modalDelete.style.border = 'none';
    modalDelete.style.borderRadius = '8px';
    modalDelete.style.color = 'white';
    modalDelete.style.cursor = 'not-allowed';
    modalDelete.style.textTransform = 'uppercase';
    modalDelete.style.letterSpacing = '0.5px';

    // Make it disabled for add operation but keep the styling
    modalDelete.disabled = true;
    modalDelete.style.opacity = '0.5';
    modalDelete.style.filter = 'saturate(0.7)';

    modal.style.display = 'flex';
}

/**
 * Closes the editing modal
 */
function closeModal() {
    const modal = document.getElementById('commentModal');
    if (modal) modal.style.display = 'none';

    // Re-enable and reset delete button
    const modalDelete = document.getElementById('modalDelete');
    if (modalDelete) {
        modalDelete.classList.remove('btn-admin-delete');
        modalDelete.innerHTML = 'Supprimer';
        modalDelete.disabled = false;
        modalDelete.style.opacity = '1';
        modalDelete.style.filter = '';
        modalDelete.style.display = 'inline-block';
        modalDelete.style.background = '';
        modalDelete.style.padding = '';
        modalDelete.style.fontSize = '';
        modalDelete.style.fontWeight = '';
        modalDelete.style.boxShadow = '';
        modalDelete.style.transition = '';
        modalDelete.style.border = '';
        modalDelete.style.borderRadius = '';
        modalDelete.style.color = '';
        modalDelete.style.cursor = '';
        modalDelete.style.textTransform = '';
        modalDelete.style.letterSpacing = '';
    }

    // Reset save button styling to default
    const modalSave = document.getElementById('modalSave');
    if (modalSave) {
        modalSave.classList.remove('btn-admin-comment');
        modalSave.textContent = 'Enregistrer';
        modalSave.style.background = '';
        modalSave.style.padding = '';
        modalSave.style.fontSize = '';
        modalSave.style.fontWeight = '';
        modalSave.style.boxShadow = '';
        modalSave.style.transition = '';
        modalSave.style.border = '';
        modalSave.style.borderRadius = '';
        modalSave.style.color = '';
        modalSave.style.cursor = '';
        modalSave.style.textTransform = '';
        modalSave.style.letterSpacing = '';
    }
}

// ============================================================================
// DATA FETCHING AND LOADING
// ============================================================================

/**
 * Fetches all posts from the server and renders them
 * Includes comprehensive error handling and debug information
 */

function fetchPosts() {
    // Build an absolute URL relative to the current document to avoid incorrect relative paths
    const url = new URL('../../controller/communityController.php', window.location.href).href;
    console.log('Fetching posts from', url);
    fetch(url)
        .then(response => {
            const status = response.status;
            const ct = response.headers.get('content-type') || '';
            return response.text().then(text => ({ status, ct, text }));
        })
        .then(({ status, ct, text }) => {
            // Update diagnostic panels
            const statusEl = document.getElementById('backofficeStatus');
            if (statusEl) statusEl.textContent = `HTTP ${status} — Content-Type: ${ct} — ${text.length} bytes`;

            // Try to parse JSON from the text
            try {
                const data = JSON.parse(text);
                posts = data || [];
                renderPosts();
            } catch (ex) {
                console.error('Failed to parse JSON from controller response', ex);
                const backofficeFeed = document.getElementById('backofficeFeed');
                if (backofficeFeed) backofficeFeed.innerHTML = '<div class="server-error">Réponse invalide du serveur — voir debug ci-dessous.<pre style="white-space:pre-wrap; color:#a33;">' + text + '</pre></div>';
            }
        })
        .catch(err => {
            console.error('Network or fetch error when requesting posts:', err);
            const backofficeFeed = document.getElementById('backofficeFeed');
            if (backofficeFeed) backofficeFeed.innerHTML = '<div class="server-error">Erreur réseau lors du chargement des publications. Voir console pour détails.</div>';
        });
}

// ============================================================================
// INITIALIZATION AND EVENT HANDLERS
// ============================================================================

/**
 * Main initialization function - runs when DOM is ready
 * Sets up event listeners and loads initial data
 */

document.addEventListener('DOMContentLoaded', function () {
    // initial load
    fetchPosts();

    // Restore sidebar collapsed state if user toggled previously
    try {
        const collapsed = localStorage.getItem('eco_sidebar_collapsed');
        const app = document.querySelector('.app');
        if (collapsed === '1' && app) app.classList.add('sidebar-collapsed');
    } catch (e) { /* ignore localStorage errors */ }

    // Sidebar toggle button
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarHandle = document.getElementById('sidebarHandle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            const app = document.querySelector('.app');
            if (!app) return;
            const collapsed = app.classList.toggle('sidebar-collapsed');
            // store preference
            try { localStorage.setItem('eco_sidebar_collapsed', collapsed ? '1' : '0'); } catch (e) { }
            // update aria state
            this.setAttribute('aria-expanded', String(!collapsed));
        });
    }
    if (sidebarHandle) {
        sidebarHandle.addEventListener('click', function (e) {
            e.preventDefault();
            const app = document.querySelector('.app');
            if (!app) return;
            const collapsed = app.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('eco_sidebar_collapsed', collapsed ? '1' : '0'); } catch (e) { }
            // also update toggle button aria if present
            if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', String(!collapsed));
        });
    }

    // Guide modal open/close
    const openGuide = document.getElementById('openGuide');
    if (openGuide) {
        openGuide.addEventListener('click', function () {
            let modal = document.getElementById('guideModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'guideModal';
                modal.innerHTML = `<div class="modal-content"><button class="close-guide" aria-label="Fermer">×</button><h2>Charte de la communauté</h2><p>Merci de respecter les autres membres, d'éviter les contenus offensants, de partager des informations vérifiées et de garder les échanges constructifs. Tout comportement abusif pourra entraîner une modération.</p><p style="margin-top:12px;font-weight:600">Principes clés:</p><ul><li>Respect mutuel</li><li>Pas de spam ni publicité</li><li>Contenus sûrs et vérifiables</li><li>Signalement des abus au support</li></ul></div>`;
                document.body.appendChild(modal);
                modal.querySelector('.close-guide').addEventListener('click', function () { modal.style.display = 'none'; });
                modal.addEventListener('click', function (e) { if (e.target === modal) modal.style.display = 'none'; });
            }
            modal.style.display = 'flex';
        });
    }

    // Listener for Modified Comments Card
    const overviewEdit = document.getElementById('overviewEdit');
    if (overviewEdit) {
        const card = overviewEdit.closest('.stat-card');
        if (card) {
            card.style.cursor = 'pointer';
            card.title = 'Cliquez pour voir les détails';
            card.addEventListener('click', showModifiedItemsModal);
        }
    }

    // event delegation
    document.addEventListener('click', function (e) {
        if (!e.target) return;

        // View post (toggle comments)
        if (e.target.classList && e.target.classList.contains('view-post-btn')) {
            const postId = e.target.getAttribute('data-post-id');
            toggleCommentsForPost(postId);
        }

        // Add comment to post
        if (e.target.classList && e.target.classList.contains('add-comment-btn')) {
            const postId = e.target.getAttribute('data-post-id');
            showAddCommentModal(postId);
        }

        // Edit post
        if (e.target.classList && e.target.classList.contains('edit-post-btn')) {
            const postId = e.target.getAttribute('data-post-id');
            showModalForPost(postId);
        }

        // Delete post
        if (e.target.classList && e.target.classList.contains('delete-post-btn')) {
            const postId = e.target.getAttribute('data-post-id');
            const btn = e.target;
            btn.disabled = true;
            const prev = btn.textContent;
            btn.textContent = 'Suppression...';
            fetch('../../controller/communityController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}`
            })
                .then(parseJsonSafe)
                .then(data => {
                    if (data && data.success) {
                        deleteCount++;
                        fetchPosts();
                    } else {
                        showBackofficeMessage('Erreur lors de la suppression: ' + (data && data.error ? data.error : 'Réponse invalide'), true);
                    }
                })
                .catch(err => {
                    console.error('Delete parse error:', err);
                    const raw = err && err.raw ? err.raw : (err && err.message ? err.message : String(err));
                    showBackofficeMessage('Erreur lors de la suppression. ' + (raw ? '\nRaw response:\n' + raw : 'Voir console pour détails.'), true);
                })
                .finally(() => { btn.disabled = false; btn.textContent = prev; });
        }

        // Edit comment
        if (e.target.classList && e.target.classList.contains('edit-comment-btn')) {
            const commentId = e.target.getAttribute('data-comment-id');
            const postId = e.target.getAttribute('data-post-id');
            showModalForComment(commentId, postId);
        }

        // Delete comment
        if (e.target.classList && e.target.classList.contains('delete-comment-btn')) {
            const commentId = e.target.getAttribute('data-comment-id');
            const btn = e.target;
            btn.disabled = true;
            const prev = btn.textContent;
            btn.textContent = 'Suppression...';
            fetch('../../controller/communityController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${commentId}`
            })
                .then(parseJsonSafe)
                .then(data => {
                    if (data && data.success) {
                        deleteCount++;
                        fetchPosts();
                    } else {
                        showBackofficeMessage('Erreur lors de la suppression: ' + (data && data.error ? data.error : 'Réponse invalide'), true);
                    }
                })
                .catch(err => {
                    console.error('Delete parse error:', err);
                    const raw = err && err.raw ? err.raw : (err && err.message ? err.message : String(err));
                    showBackofficeMessage('Erreur lors de la suppression. ' + (raw ? '\nRaw response:\n' + raw : 'Voir console pour détails.'), true);
                })
                .finally(() => { btn.disabled = false; btn.textContent = prev; });
        }

        // Toolbar: refresh
        if (e.target.id === 'refreshFeed') {
            fetchPosts();
        }
    });
});

// ============================================================================
// PROFESSIONAL ANIMATIONS - Ripple Effect & Page Transitions
// ============================================================================

/**
 * Create ripple effect on button clicks
 * @param {Event} e - Click event
 */
function createRipple(e) {
    const button = e.currentTarget;

    // Don't add ripple if button is disabled
    if (button.disabled) return;

    // Remove any existing ripples
    const existingRipple = button.querySelector('.ripple');
    if (existingRipple) {
        existingRipple.remove();
    }

    const circle = document.createElement('span');
    const diameter = Math.max(button.clientWidth, button.clientHeight);
    const radius = diameter / 2;

    const rect = button.getBoundingClientRect();
    circle.style.width = circle.style.height = `${diameter}px`;
    circle.style.left = `${e.clientX - rect.left - radius}px`;
    circle.style.top = `${e.clientY - rect.top - radius}px`;
    circle.classList.add('ripple');

    button.appendChild(circle);

    // Remove ripple after animation
    setTimeout(() => circle.remove(), 600);
}

/**
 * Add ripple effect to all buttons
 */
function initializeRippleEffects() {
    const buttons = document.querySelectorAll('.btn, .btn-primary, .btn-secondary, .btn-danger, .btn-admin-comment, .btn-admin-delete, button');
    buttons.forEach(button => {
        // Remove existing listener if any
        button.removeEventListener('click', createRipple);
        // Add ripple effect
        button.addEventListener('click', createRipple);
    });
}

/**
 * Create page transition overlay
 */
function createPageTransitionOverlay() {
    if (document.querySelector('.page-transition-overlay')) return;

    const overlay = document.createElement('div');
    overlay.className = 'page-transition-overlay';
    overlay.innerHTML = `
        <div style="text-align: center;">
            <div class="page-loader"></div>
            <div class="page-loader-text">Chargement...</div>
        </div>
    `;
    document.body.appendChild(overlay);
}

/**
 * Show page transition with animation
 * @param {string} url - URL to navigate to
 */
function navigateWithTransition(url) {
    const overlay = document.querySelector('.page-transition-overlay');
    if (!overlay) {
        createPageTransitionOverlay();
        // Wait a bit for overlay to be created
        setTimeout(() => navigateWithTransition(url), 10);
        return;
    }

    // Show transition
    overlay.classList.add('active');

    // Navigate after animation
    setTimeout(() => {
        window.location.href = url;
    }, 400);
}

/**
 * Initialize page transition for navigation links
 */
function initializePageTransitions() {
    // Create overlay on page load
    createPageTransitionOverlay();

    // Add smooth transition to frontoffice link
    const navLinks = document.querySelectorAll('a[href*="frontoffice"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.href;
            navigateWithTransition(url);
        });
    });
}

// Initialize all animations when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    initializeRippleEffects();
    initializePageTransitions();
});

// Re-initialize ripple effects after posts are rendered
const originalRenderPosts = renderPosts;
renderPosts = function (postsToRender = null) {
    originalRenderPosts(postsToRender);
    // Add delay to ensure DOM is updated
    setTimeout(initializeRippleEffects, 100);
};

// ============================================================================
// SEARCH AND FILTER FUNCTIONALITY
// ============================================================================

/**
 * Filter posts based on search input and filter type
 */
function filterAndRender() {
    const searchInput = document.getElementById('searchInput');
    const filterType = document.getElementById('filterType');
    const searchResults = document.getElementById('searchResults');

    if (!searchInput || !filterType) return;

    const query = searchInput.value.toLowerCase().trim();
    const type = filterType.value; // 'all', 'posts', 'comments'

    // If empty query, render all posts
    if (!query) {
        renderPosts(null); // null means render all global posts
        if (searchResults) searchResults.textContent = '';
        return;
    }

    // Filter logic
    const filteredPosts = posts.filter(post => {
        const postContent = (post.contenu || '').toLowerCase();
        const postAuthor = (post.send_by || '').toLowerCase();

        // Check post matches
        const postMatches = postContent.includes(query) || postAuthor.includes(query);

        // Check comment matches
        const comments = post.comments || [];
        const commentMatches = comments.some(comment => {
            const commentContent = (comment.contenu || '').toLowerCase();
            const commentAuthor = (comment.send_by || '').toLowerCase();
            return commentContent.includes(query) || commentAuthor.includes(query);
        });

        if (type === 'posts') {
            return postMatches;
        } else if (type === 'comments') {
            return commentMatches;
        } else {
            // 'all'
            return postMatches || commentMatches;
        }
    });

    // Render filtered results
    renderPosts(filteredPosts);

    // Update results text
    if (searchResults) {
        const count = filteredPosts.length;
        if (count === 0) {
            searchResults.textContent = 'Aucun résultat trouvé.';
        } else {
            searchResults.textContent = `${count} résultat(s) trouvé(s)`;
        }
    }
}

// Initialize search listeners
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const filterType = document.getElementById('filterType');
    const clearSearch = document.getElementById('clearSearch');

    if (searchInput) {
        searchInput.addEventListener('input', filterAndRender);
    }

    if (filterType) {
        filterType.addEventListener('change', filterAndRender);
    }

    if (clearSearch) {
        clearSearch.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                filterAndRender();
                searchInput.focus();
            }
        });
    }
});
