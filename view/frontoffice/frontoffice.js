const createPostBtn = document.getElementById('createPostBtn');
const modal = document.getElementById('postModal');
const closeModal = document.getElementById('closeModal');
const submitPost = document.getElementById('submitPost');
const postContent = document.getElementById('postContent');
const postAuthor = document.getElementById('postAuthor');
const ecoFeed = document.getElementById('ecoFeed');
const postsContainer = document.getElementById('postsContainer');
const postMessage = document.getElementById('postMessage');
let editingPostId = null; // null => creating new post; otherwise updating existing post id
let editingCommentId = null; // when editing a post, which comment id is being edited (main comment)
const API_URL = new URL('../../controller/communityController.php', window.location.href).href;
console.log('frontoffice.js loaded — API:', API_URL);
window.addEventListener('error', function (ev) { console.error('Global JS error', ev); });
window.addEventListener('unhandledrejection', function (ev) { console.error('Unhandled promise rejection', ev); });

// Helper: parse JSON only if server returned JSON
function parseJsonSafe(response) {
    const ct = response.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
        return response.text().then(text => { throw new Error('Invalid JSON response:\n' + text); });
    }
    return response.json();
}

// Escape HTML to avoid XSS when rendering user-submitted content
function escapeHtml(unsafe) {
    return String(unsafe)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Ouvre la modale de création de post
createPostBtn.addEventListener('click', () => {
    // show modal when user clicks the button (centered with animation)
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');

        // Hide hero section
        const heroSection = document.querySelector('.hero-communication');
        if (heroSection) {
            heroSection.style.display = 'none';
        }

        // small timeout to allow CSS transition and then focus content
        setTimeout(() => {
            postContent.focus();
            // auto-resize the textarea to fit current content
            autoResizeTextarea(postContent);
        }, 120);
        console.log('createPostBtn clicked — modal displayed');
    }
});

// Ferme la modale
closeModal.addEventListener('click', () => {
    if (modal) {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        // hide after transition
        setTimeout(() => { modal.style.display = 'none'; }, 260);
    }

    // Show hero section again
    const heroSection = document.querySelector('.hero-communication');
    if (heroSection) {
        heroSection.style.display = 'block';
    }

    postContent.value = '';
    postAuthor.value = '';
    postMessage.style.display = 'none';
    editingPostId = null; editingCommentId = null;
    // Ensure fields and submit are re-enabled when modal closes
    postAuthor.disabled = false;
    postContent.disabled = false;
    submitPost.disabled = false;
    submitPost.style.display = '';
});

// Ferme la modale si on clique dehors
window.addEventListener('click', (e) => {
    if (e.target == modal) {
        if (modal) {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            setTimeout(() => { modal.style.display = 'none'; }, 260);
        }

        // Show hero section again
        const heroSection = document.querySelector('.hero-communication');
        if (heroSection) {
            heroSection.style.display = 'block';
        }

        postContent.value = '';
        postAuthor.value = '';
        editingPostId = null; editingCommentId = null;
        // Re-enable inputs and submit
        postAuthor.disabled = false;
        postContent.disabled = false;
        submitPost.disabled = false;
        submitPost.style.display = '';
    }
});

// Fetch and render comments (used as posts)
function renderComments(list) {
    // Remove previously rendered server posts (keep any static HTML examples)
    const previous = postsContainer.querySelectorAll('.server-post');
    previous.forEach(n => n.remove());
    if (!Array.isArray(list) || list.length === 0) {
        // nothing to add from server — keep static content
        return;
    }
    // Append server posts (do not remove static example)
    list.forEach(item => {
        const article = document.createElement('article');
        article.className = 'publication server-post';
        article.dataset.postId = item.id;
        const sendBy = item.send_by || 'Anonyme';
        const contenu = item.contenu || '';
        const time = item.time || '';
        const commentCount = (item.comments && Array.isArray(item.comments)) ? item.comments.length : 0;

        // Build attachment HTML if present
        let attachmentHTML = '';
        if (item.attachment) {
            const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(item.attachment);
            if (isImage) {
                attachmentHTML = `<div style="margin-top:8px;"><img src="../../${escapeHtml(item.attachment)}" style="max-width:300px;max-height:300px;border-radius:6px;border:1px solid #ddd;" alt="Attachment" /></div>`;
            } else {
                const fileName = item.attachment.split('/').pop();
                attachmentHTML = `<div style="margin-top:8px;"><a href="../../${escapeHtml(item.attachment)}" target="_blank" style="color:#357a38;text-decoration:none;">📎 ${escapeHtml(fileName)}</a></div>`;
            }
        }

        // Build a post card with management buttons and a collapsible comments section
        article.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                <div style="flex:1">
                    <p><strong>${escapeHtml(sendBy)}</strong>: <span class="post-body">${escapeHtml(contenu)}</span></p>
                    ${attachmentHTML}
                    <div class="pub-date">${time} • ${commentCount} commentaire(s)</div>
                </div>
                <div style="flex:0 0 auto;">
                    <div class="actions" style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
                        <button class="view-post btn btn-secondary" data-id="${item.id}" aria-label="Voir la publication">Voir</button>
                        <button class="edit-post btn btn-primary" data-id="${item.id}" aria-label="Modifier la publication">Modifier</button>
                        <button class="delete-post btn btn-danger" data-id="${item.id}" aria-label="Supprimer la publication">Supprimer</button>
                    </div>
                </div>
            </div>
            <div class="comments-section" data-post-id="${item.id}" style="display:none;margin-top:16px;padding-top:16px;border-top:1px solid #e6e6e6;">
                <h4 style="margin:0 0 12px 0;color:#2b3b36;">Commentaires (${commentCount})</h4>
                <div class="comments-display" style="margin-bottom:12px;"></div>
            </div>
            <div class="comments-zone" style="margin-top:10px;padding-top:10px;border-top:1px solid #eee;">
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input class="comment-author" placeholder="Votre nom (optionnel)" style="width:160px;padding:8px;border:1px solid #ddd;border-radius:6px;" />
                    <input class="comment-input" placeholder="Écrire un commentaire..." style="flex:1;min-width:200px;padding:8px;border:1px solid #ddd;border-radius:6px;" />
                    <input class="comment-attachment" type="file" accept="image/*,.pdf,.doc,.docx,.txt" style="display:none;" data-post-id="${item.id}" />
                    <button class="attach-file-btn btn btn-secondary" data-post-id="${item.id}" style="padding:8px 12px;">📎</button>
                    <button class="comment-btn btn btn-primary" data-parent-id="${item.id}">Commenter</button>
                </div>
                <div class="comment-file-preview" style="margin-top:8px;display:none;font-size:0.9em;color:#666;">
                    <span class="comment-file-name"></span>
                    <button type="button" class="remove-comment-file" style="margin-left:8px;background:#d9534f;color:white;border:none;padding:2px 6px;border-radius:4px;cursor:pointer;font-size:0.85em;">✕</button>
                </div>
            </div>
        `;
        postsContainer.appendChild(article);
    });
}

function fetchComments() {
    fetch(API_URL)
        .then(parseJsonSafe)
        .then(data => {
            console.log('create post returned:', data);
            renderComments(data);
        })
        .catch(err => {
            console.error('create post fetch error', err);
            console.warn('Fetch comments error — showing empty feed:', err);
            // Show empty feed instead of error message to avoid alarming users
            renderComments([]);
        });
}

// Load existing comments on open
document.addEventListener('DOMContentLoaded', () => {
    fetchComments();
    // Wire the community guide modal in frontoffice
    const openGuideFront = document.getElementById('openGuideFront');
    if (openGuideFront) {
        openGuideFront.addEventListener('click', function (e) {
            e.preventDefault();
            let modal = document.getElementById('guideModalFront');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'guideModalFront';
                modal.className = 'modal';
                modal.innerHTML = `<div class="modal-content"><button class="close" aria-label="Fermer">×</button><h2>Charte de la communauté</h2><p>Merci de respecter les autres membres, d'éviter les contenus offensants, de partager des informations vérifiées et de garder les échanges constructifs. Tout comportement abusif pourra entraîner une modération.</p><p style="margin-top:12px;font-weight:600">Principes clés:</p><ul><li>Respect mutuel</li><li>Pas de spam ni publicité</li><li>Contenus sûrs et vérifiables</li><li>Signalez les abus au support</li></ul></div>`;
                document.body.appendChild(modal);
                modal.querySelector('.close').addEventListener('click', function () { modal.style.display = 'none'; });
                modal.addEventListener('click', function (ev) { if (ev.target === modal) modal.style.display = 'none'; });
            }
            modal.style.display = 'flex';
        });
    }

    // textarea auto-resize helper (keeps UX nicer and provides more space)
    function autoResizeTextarea(el) {
        if (!el) return;
        el.style.height = 'auto';
        const newH = Math.max(120, Math.min(600, el.scrollHeight));
        el.style.height = newH + 'px';
    }

    // Wire auto-resize on input for the modal textarea
    if (postContent) {
        postContent.addEventListener('input', function () { autoResizeTextarea(this); });
        // ensure a sensible starting height
        autoResizeTextarea(postContent);
    }

    // File upload preview handling
    const postAttachment = document.getElementById('postAttachment');
    const filePreview = document.getElementById('filePreview');
    const previewImage = document.getElementById('previewImage');
    const previewFile = document.getElementById('previewFile');
    const fileName = document.getElementById('fileName');
    const removeFile = document.getElementById('removeFile');

    if (postAttachment) {
        postAttachment.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) {
                filePreview.style.display = 'none';
                return;
            }

            // Show preview
            filePreview.style.display = 'block';

            // Check if it's an image
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                    previewFile.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                // Show file name for non-images
                previewImage.style.display = 'none';
                previewFile.style.display = 'block';
                fileName.textContent = file.name;
            }
        });
    }

    if (removeFile) {
        removeFile.addEventListener('click', function () {
            if (postAttachment) postAttachment.value = '';
            filePreview.style.display = 'none';
            previewImage.style.display = 'none';
            previewFile.style.display = 'none';
        });
    }

    // Handle comment file input changes (event delegation)
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('comment-attachment')) {
            const zone = e.target.closest('.comments-zone');
            const preview = zone.querySelector('.comment-file-preview');
            const fileNameSpan = zone.querySelector('.comment-file-name');

            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                if (fileNameSpan) fileNameSpan.textContent = `📎 ${file.name}`;
                if (preview) preview.style.display = 'block';
            } else {
                if (preview) preview.style.display = 'none';
            }
        }
    });


    // Cancel button handler
    const cancelPost = document.getElementById('cancelPost');
    if (cancelPost) {
        cancelPost.addEventListener('click', function () {
            // Close modal
            if (modal) {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                setTimeout(() => { modal.style.display = 'none'; }, 260);
            }

            // Show hero section again
            const heroSection = document.querySelector('.hero-communication');
            if (heroSection) {
                heroSection.style.display = 'block';
            }

            // Clear form fields
            postContent.value = '';
            postAuthor.value = '';
            if (postAttachment) postAttachment.value = '';
            filePreview.style.display = 'none';
            postMessage.style.display = 'none';
            editingPostId = null;
            editingCommentId = null;

            // Re-enable inputs
            postAuthor.disabled = false;
            postContent.disabled = false;
            submitPost.disabled = false;
            submitPost.style.display = '';
        });
    }

    // Sidebar slide/collapse wiring: preserve state in localStorage
    const root = document.querySelector('.app');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarHandle = document.getElementById('sidebarHandle');
    const SIDEBAR_KEY = 'eco_sidebar_collapsed';

    function setSidebarCollapsed(collapsed) {
        if (!root) return;
        if (collapsed) root.classList.add('sidebar-collapsed');
        else root.classList.remove('sidebar-collapsed');
        try { localStorage.setItem(SIDEBAR_KEY, collapsed ? '1' : '0'); } catch (e) { /* ignore */ }
    }

    // Restore saved state
    try {
        const saved = localStorage.getItem(SIDEBAR_KEY);
        if (saved === '1') setSidebarCollapsed(true);
    } catch (e) { /* ignore */ }

    function toggleSidebar() { setSidebarCollapsed(!root.classList.contains('sidebar-collapsed')); }

    if (sidebarToggle) sidebarToggle.addEventListener('click', (e) => { e.preventDefault(); toggleSidebar(); });
    if (sidebarHandle) sidebarHandle.addEventListener('click', (e) => { e.preventDefault(); toggleSidebar(); });
    // Move publish click handler registration here (after DOM is ready)
    if (submitPost) {
        submitPost.addEventListener('click', (e) => {
            if (e && typeof e.preventDefault === 'function') e.preventDefault();
            console.log('submitPost clicked', { editingPostId, editingCommentId, contentPreview: postContent ? postContent.value : null });
            const content = postContent ? postContent.value.trim() : '';
            const sendBy = (postAuthor && postAuthor.value ? postAuthor.value : 'Anonyme').trim();
            if (content.length === 0) {
                alert('Veuillez écrire quelque chose pour publier.');
                return;
            }
            submitPost.disabled = true;
            // Play animation immediately to show the post is being published
            try {
                postMessage.style.display = 'inline';
                postMessage.textContent = 'Publication en cours...';
            } catch (e) { }
            const now = new Date().toLocaleString();
            // Optimistic local append — show the post immediately while saving (ONLY for new posts)
            let localEl = null;
            if (!editingPostId) {
                localEl = appendLocalPost(sendBy, content, now, 'Enregistrement...', 'saving');
            }
            // If editingPostId is set, update existing post; otherwise create new
            if (editingPostId) {
                console.log('Update post payload', { post_id: editingPostId, send_by: sendBy, contenu: content });
                const formData = new FormData();
                formData.append('post_id', editingPostId);
                formData.append('contenu', content);
                formData.append('send_by', sendBy);
                // Add file if selected
                if (postAttachment && postAttachment.files.length > 0) {
                    formData.append('attachment', postAttachment.files[0]);
                }
                fetch(API_URL, {
                    method: 'POST',
                    body: formData
                }).then(parseJsonSafe).then(data => {
                    console.log('Update post response', data);
                    if (data && data.success) {
                        editingPostId = null; editingCommentId = null;
                        if (modal) {
                            modal.classList.remove('open');
                            modal.setAttribute('aria-hidden', 'true');
                            setTimeout(() => { modal.style.display = 'none'; }, 260);
                        }
                        postContent.value = ''; postAuthor.value = '';
                        if (postAttachment) postAttachment.value = '';
                        filePreview.style.display = 'none';
                        fetchComments();
                    } else {
                        alert('Erreur lors de la mise à jour: ' + (data && data.error ? data.error : ''));
                    }
                }).catch(err => { console.error('Update error', err); alert('Erreur lors de la mise à jour.'); })
                    .finally(() => { submitPost.disabled = false; });
            } else {
                // Envoi du post au backend (create)
                console.log('Create post payload', { send_by: sendBy, contenu: content });
                const formData = new FormData();
                formData.append('send_by', sendBy);
                formData.append('contenu', content);
                // Add file if selected
                if (postAttachment && postAttachment.files.length > 0) {
                    formData.append('attachment', postAttachment.files[0]);
                }
                fetch(API_URL, {
                    method: 'POST',
                    body: formData
                })
                    .then(parseJsonSafe)
                    .then(data => {
                        console.log('Create post response', data);
                        if (data && data.success) {
                            // On success: remove local optimistic post and refresh feed
                            if (localEl && localEl.parentNode) localEl.parentNode.removeChild(localEl);
                            // show a small success animation inside the modal, then close
                            postMessage.style.display = 'inline';
                            postMessage.textContent = 'Publié';
                            fetchComments();
                            try {
                                const inner = modal.querySelector('.modal-content');
                                let success = inner.querySelector('.publish-success');
                                if (!success) {
                                    success = document.createElement('div');
                                    success.className = 'publish-success';
                                    success.innerHTML = '<div class="check">✓</div>';
                                    inner.appendChild(success);
                                }
                                // trigger animation
                                setTimeout(() => success.classList.add('show'), 40);
                                // close after show
                                setTimeout(() => {
                                    success.classList.remove('show');
                                    // hide modal after animation
                                    modal.classList.remove('open');
                                    modal.setAttribute('aria-hidden', 'true');
                                    setTimeout(() => { modal.style.display = 'none'; }, 260);
                                    postMessage.style.display = 'none';
                                    postContent.value = '';
                                    postAuthor.value = '';
                                }, 900);
                            } catch (e) {
                                // fallback: close quickly
                                modal.classList.remove('open');
                                setTimeout(() => { modal.style.display = 'none'; }, 260);
                                postMessage.style.display = 'none';
                                postContent.value = '';
                                postAuthor.value = '';
                            }
                        } else {
                            // server returned JSON with error: add post locally and inform user
                            const now = new Date().toLocaleString();
                            appendLocalPost(sendBy, content, now, '(non sauvegardé: ' + (data.error || 'erreur serveur') + ')');
                            alert('Le post a été ajouté localement mais le serveur a renvoyé une erreur: ' + (data.error || ''));
                        }
                    })
                    .catch(err => {
                        console.warn('Submit error — adding post locally:', err);
                        if (localEl) {
                            // Mark local post as failed so user can retry
                            const note = '(non sauvegardé: serveur indisponible)';
                            const timeEl = localEl.querySelector('.pub-date');
                            if (timeEl) timeEl.innerHTML = `${now} <span style="color:#a33; font-weight:600;">${note}</span>`;
                            localEl.classList.add('failed');
                            const retryBtn = localEl.querySelector('.retry-post');
                            if (!retryBtn) {
                                const actionsDiv = localEl.querySelector('[style*="flex-direction:column"]');
                                if (actionsDiv) {
                                    const btn = document.createElement('button');
                                    btn.className = 'retry-post btn btn-secondary';
                                    btn.textContent = 'Réessayer';
                                    btn.addEventListener('click', function () {
                                        // Retry by calling the API again
                                        btn.disabled = true;
                                        fetch(API_URL, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `send_by=${encodeURIComponent(sendBy)}&contenu=${encodeURIComponent(content)}` })
                                            .then(parseJsonSafe)
                                            .then(data => {
                                                if (data && data.success) {
                                                    if (localEl && localEl.parentNode) localEl.parentNode.removeChild(localEl);
                                                    fetchComments();
                                                } else {
                                                    alert('Erreur lors de la ré-essai: ' + (data && data.error ? data.error : ''));
                                                }
                                            }).catch(err2 => { alert('Ré-essai impossible: ' + (err2.message || 'erreur serveur')); })
                                            .finally(() => btn.disabled = false);
                                    });
                                    actionsDiv.appendChild(btn);
                                }
                            }
                        } else {
                            appendLocalPost(sendBy, content, now, '(publié localement, serveur indisponible)');
                        }
                    })
                    .finally(() => { submitPost.disabled = false; });
            }
        });
    }
});

/**
 * Toggle visibility of comments section for a specific post
 * Similar to the backoffice implementation
 * @param {string|number} postId - ID of the post to toggle comments for
 */
function toggleCommentsForPost(postId) {
    const commentsSection = document.querySelector(`.comments-section[data-post-id="${postId}"]`);
    if (!commentsSection) return;

    const isVisible = commentsSection.style.display !== 'none';
    if (isVisible) {
        // Hide comments section
        commentsSection.style.display = 'none';
    } else {
        // Show and render comments
        fetch(API_URL)
            .then(parseJsonSafe)
            .then(data => {
                const post = (data || []).find(p => String(p.id) === String(postId));
                if (!post) return;

                const commentsDisplay = commentsSection.querySelector('.comments-display');
                commentsDisplay.innerHTML = '';

                if (!post.comments || post.comments.length === 0) {
                    commentsDisplay.innerHTML = '<div style="color:#666;font-style:italic;">Aucun commentaire</div>';
                } else {
                    post.comments.forEach(comment => {
                        const commentDiv = document.createElement('div');
                        commentDiv.className = 'comment-item'; // Added class for event delegation
                        commentDiv.style.background = '#f9f9f9';
                        commentDiv.style.padding = '10px';
                        commentDiv.style.marginBottom = '8px';
                        commentDiv.style.borderRadius = '6px';
                        commentDiv.style.border = '1px solid #e0e0e0';

                        const author = escapeHtml(comment.send_by || 'Anonyme');
                        const body = escapeHtml(comment.contenu || '');

                        // Build attachment HTML if present
                        let commentAttachmentHTML = '';
                        if (comment.attachment) {
                            const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(comment.attachment);
                            if (isImage) {
                                commentAttachmentHTML = `<div style="margin-top:6px;"><img src="../../${escapeHtml(comment.attachment)}" style="max-width:200px;max-height:200px;border-radius:4px;border:1px solid #ccc;" alt="Attachment" /></div>`;
                            } else {
                                const fileName = comment.attachment.split('/').pop();
                                commentAttachmentHTML = `<div style="margin-top:6px;"><a href="../../${escapeHtml(comment.attachment)}" target="_blank" style="color:#357a38;text-decoration:none;font-size:0.9em;">📎 ${escapeHtml(fileName)}</a></div>`;
                            }
                        }

                        commentDiv.innerHTML = `
                            <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;">
                                <div style="flex:1">
                                    <div class="comment-author-label" style="font-weight:600;color:#2b3b36;margin-bottom:4px;">${author}</div>
                                    <div class="comment-content" style="color:#333;">${body}</div>
                                    ${commentAttachmentHTML}
                                </div>
                                <div style="flex:0 0 auto;display:flex;gap:6px;">
                                    <button class="edit-comment btn btn-sm btn-primary" data-comment-id="${comment.id}" style="padding:6px 8px;border-radius:4px;font-size:0.85em;">Modifier</button>
                                    <button class="delete-comment btn btn-sm btn-danger" data-comment-id="${comment.id}" style="padding:6px 8px;border-radius:4px;font-size:0.85em;">Supprimer</button>
                                </div>
                            </div>
                        `;
                        commentsDisplay.appendChild(commentDiv);
                    });
                }

                commentsSection.style.display = 'block';
            })
            .catch(err => {
                console.error('Error fetching comments:', err);
                alert('Impossible de charger les commentaires');
            });
    }
}

// (publish handler now attached above within DOMContentLoaded; old duplicate removed)

// Handle comment, edit, delete and view actions via event delegation
postsContainer.addEventListener('click', function (e) {
    // Handle attach file button for comments
    const attachBtn = e.target.closest('.attach-file-btn');
    if (attachBtn) {
        const postId = attachBtn.getAttribute('data-post-id');
        const fileInput = document.querySelector(`.comment-attachment[data-post-id="${postId}"]`);
        if (fileInput) fileInput.click();
        return;
    }

    // Handle remove comment file
    const removeCommentFile = e.target.closest('.remove-comment-file');
    if (removeCommentFile) {
        const zone = removeCommentFile.closest('.comments-zone');
        const fileInput = zone.querySelector('.comment-attachment');
        const preview = zone.querySelector('.comment-file-preview');
        if (fileInput) fileInput.value = '';
        if (preview) preview.style.display = 'none';
        return;
    }

    // Comment on a post (creates a new comment record)
    const clickedCommentBtn = e.target.closest('.comment-btn');
    if (clickedCommentBtn) {
        const container = clickedCommentBtn.closest('.comments-zone');
        const input = container.querySelector('.comment-input');
        const authorInput = container.querySelector('.comment-author'); // (no change, just for context)
        const text = input.value.trim();
        const author = (authorInput && authorInput.value.trim()) || 'Anonyme';
        console.log('Comment submit', { parentId: clickedCommentBtn.getAttribute('data-parent-id'), author, text });
        if (text.length === 0) { alert('Écris un commentaire avant de répondre.'); return; }
        const parentId = clickedCommentBtn.getAttribute('data-parent-id');
        // Add comment to existing post using parent_id
        const formData = new FormData();
        formData.append('parent_id', parentId);
        formData.append('contenu', text);
        formData.append('send_by', author);
        // Add file if selected
        const commentZone = clickedCommentBtn.closest('.comments-zone');
        const fileInput = commentZone.querySelector('.comment-attachment');
        if (fileInput && fileInput.files.length > 0) {
            formData.append('attachment', fileInput.files[0]);
        }
        fetch(API_URL, { method: 'POST', body: formData })
            .then(parseJsonSafe)
            .then(data => {
                if (data && data.success) {
                    input.value = '';
                    if (authorInput) authorInput.value = '';
                    fetchComments();
                } else {
                    alert('Erreur lors de l\'ajout du commentaire: ' + (data && data.error ? data.error : ''));
                }
            })
            .catch(err => { console.error('Comment submit error', err); alert('Impossible d\'envoyer le commentaire.'); });
    }

    // Edit post: open modal pre-filled so user can edit content (and submit updates)
    const clickedEditPost = e.target.closest('.edit-post');
    if (clickedEditPost) {
        const id = clickedEditPost.getAttribute('data-id');
        // fetch the single post data then open modal for editing
        fetch(API_URL).then(parseJsonSafe).then(data => {
            const post = (data || []).find(p => String(p.id) === String(id));
            if (!post) { alert('Publication introuvable.'); return; }
            document.getElementById('postAuthor').value = post.send_by || '';
            document.getElementById('postContent').value = post.contenu || '';
            // Display modal using the same UI flow as the Create modal
            modal.style.display = 'flex';
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            // Enable fields for editing and show submit
            postAuthor.disabled = false;
            postContent.disabled = false;
            submitPost.disabled = false;
            submitPost.style.display = '';
            // Set editingPostId to the post id and editingCommentId to the id of the main comment (if any)
            editingPostId = id;
            console.log('editingPostId set to', editingPostId);
            editingCommentId = null;
        }).catch(err => { console.error('Edit fetch error', err); alert('Impossible d\'ouvrir la publication pour modification.'); });
    }

    // Delete post (no confirm dialog — perform deletion immediately)
    const clickedDeletePost = e.target.closest('.delete-post');
    if (clickedDeletePost) {
        const id = clickedDeletePost.getAttribute('data-id');
        const btn = e.target;
        btn.disabled = true;
        const prevText = btn.textContent;
        btn.textContent = 'Suppression...';
        fetch(API_URL, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `post_id=${id}` })
            .then(parseJsonSafe)
            .then(data => {
                if (data && data.success) { fetchComments(); } else { alert('Erreur lors de la suppression: ' + (data && data.error ? data.error : '')); }
            })
            .catch(err => { console.error('Delete error', err); alert('Erreur lors de la suppression.'); })
            .finally(() => { btn.disabled = false; btn.textContent = prevText; });
    }


    // View post - toggle comments section (like in backoffice)
    const clickedViewPost = e.target.closest('.view-post');
    if (clickedViewPost) {
        const id = clickedViewPost.getAttribute('data-id');
        toggleCommentsForPost(id);
    }


    // Edit/delete comments (future: only allow if user is author or admin)
    const clickedEditComment = e.target.closest('.edit-comment');
    if (clickedEditComment) {
        const commentEl = clickedEditComment.closest('.comment-item');
        if (!commentEl) return;
        const contentEl = commentEl.querySelector('.comment-content');
        const authorEl = commentEl.querySelector('.comment-author-label');
        const editInput = document.createElement('input');
        editInput.type = 'text';
        editInput.value = contentEl.textContent;
        editInput.className = 'comment-edit-input';
        contentEl.replaceWith(editInput);
        e.target.style.display = 'none';
        const saveBtn = document.createElement('button');
        saveBtn.textContent = 'Enregistrer';
        saveBtn.className = 'btn btn-primary save-comment';
        e.target.parentNode.insertBefore(saveBtn, e.target.nextSibling);
        saveBtn.addEventListener('click', function () {
            const newContent = editInput.value.trim();
            if (!newContent) return alert('Le commentaire ne peut pas être vide.');
            const commentId = clickedEditComment.getAttribute('data-comment-id');
            console.log('Updating comment', { commentId, newContent });
            // send update to backend with comment id
            fetch(API_URL, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `id=${encodeURIComponent(commentId)}&contenu=${encodeURIComponent(newContent)}` })
                .then(parseJsonSafe)
                .then(data => {
                    if (data && data.success) {
                        fetchComments();
                    } else {
                        alert('Erreur lors de la mise à jour du commentaire: ' + (data && data.error ? data.error : ''));
                    }
                }).catch(err => { console.error('Update comment error', err); alert('Impossible de mettre à jour le commentaire.'); });
            editInput.replaceWith(contentEl);
            saveBtn.remove();
            e.target.style.display = '';
        });
    }
    const clickedDeleteComment = e.target.closest('.delete-comment');
    if (clickedDeleteComment) {
        const commentEl = clickedDeleteComment.closest('.comment-item');
        if (!commentEl) return;
        const commentId = clickedDeleteComment.getAttribute('data-comment-id');
        const btn = e.target;
        btn.disabled = true;
        const prevText = btn.textContent;
        btn.textContent = 'Suppression...';
        fetch(API_URL, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `id=${encodeURIComponent(commentId)}` })
            .then(parseJsonSafe)
            .then(data => {
                if (data && data.success) { fetchComments(); } else { alert('Erreur lors de la suppression: ' + (data && data.error ? data.error : '')); }
            })
            .catch(err => { console.error('Delete comment error', err); alert('Erreur lors de la suppression du commentaire.'); })
            .finally(() => { btn.disabled = false; btn.textContent = prevText; });
    }
});

// Append a local-only post to the top of the list with an optional note
function appendLocalPost(author, content, time, note, status = 'saving') {
    const article = document.createElement('article');
    article.className = 'publication local-post';
    article.setAttribute('data-local', '1');
    const sendBy = author || 'Anonyme';
    const statusText = status === 'saving' ? '<span class="local-status saving">Enregistrement...</span>' : `<span class="local-status failed">${note}</span>`;
    // add a small actions area: retry if failed
    article.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
            <div style="flex:1">
                <p><strong>${escapeHtml(sendBy)}</strong>: <span class="post-body">${escapeHtml(content)}</span></p>
                <div class="pub-date">${time} ${status === 'failed' ? '<span style="color:#a33; font-weight:600;">' + escapeHtml(note) + '</span>' : statusText}</div>
            </div>
            <div style="flex:0 0 auto;display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
                ${status === 'failed' ? '<button class="retry-post btn btn-secondary">Réessayer</button>' : ''}
            </div>
        </div>
    `;
    // insert at top
    if (postsContainer.firstChild) postsContainer.insertBefore(article, postsContainer.firstChild);
    else postsContainer.appendChild(article);
    return article;
}

// ============================================================================
// PROFESSIONAL ANIMATIONS - Ripple Effect & Page Transitions
// ============================================================================

/**
 * Create ripple effect on button clicks
 * @param {Event} e - Click event
 */
function createRipple(e) {
    const button = e.currentTarget;

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
    const buttons = document.querySelectorAll('.btn, .comment-btn, button');
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

    // Add smooth transition to backoffice link
    const navLinks = document.querySelectorAll('a[href*="backoffice"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.href;
            navigateWithTransition(url);
        });
    });
}

// Initialize animations when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    initializeRippleEffects();
    initializePageTransitions();
});

// Re-initialize ripple effects after new content is added (after fetching comments)
const originalFetchComments = fetchComments;
fetchComments = function () {
    originalFetchComments();
    // Add delay to ensure DOM is updated
    setTimeout(initializeRippleEffects, 100);
};
