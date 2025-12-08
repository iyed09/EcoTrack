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

/**
 * Show animated error modal with message
 * @param {string} message - Error message to display
 */
function showErrorModal(message) {
    const errorModal = document.getElementById('errorModal');
    const errorMessage = document.getElementById('errorMessage');

    if (!errorModal || !errorMessage) {
        console.error('Error modal elements not found');
        alert(message); // Fallback to alert if modal not found
        return;
    }

    // Set the error message
    errorMessage.textContent = message;

    // Show modal with animation
    errorModal.style.display = 'flex';
    errorModal.setAttribute('aria-hidden', 'false');

    // Trigger animation by adding 'open' class after a brief delay
    setTimeout(() => {
        errorModal.classList.add('open');
    }, 10);

    // Auto-dismiss after 4 seconds
    const autoDismissTimeout = setTimeout(() => {
        closeErrorModal();
    }, 4000);

    // Store timeout ID so it can be cleared if user closes manually
    errorModal.dataset.autoDismissTimeout = autoDismissTimeout;
}

/**
 * Close error modal with animation
 */
function closeErrorModal() {
    const errorModal = document.getElementById('errorModal');
    if (!errorModal) return;

    // Clear auto-dismiss timeout if exists
    if (errorModal.dataset.autoDismissTimeout) {
        clearTimeout(parseInt(errorModal.dataset.autoDismissTimeout));
        delete errorModal.dataset.autoDismissTimeout;
    }

    // Remove 'open' class to trigger exit animation
    errorModal.classList.remove('open');

    // Hide modal after animation completes
    setTimeout(() => {
        errorModal.style.display = 'none';
        errorModal.setAttribute('aria-hidden', 'true');
    }, 300);
}


/**
 * Records a modification in localStorage for backoffice stats tracking
 * @param {string} type - 'post' or 'comment's
 * @param {string|number} id - The ID of the modified item
 * @param {string} author - The author name
 * @param {string} content - The new content
 * @param {string} originalContent - The original content before modification
 */
function recordModificationForBackoffice(type, id, author, content, originalContent) {
    try {
        const modification = {
            type: type === 'post' ? 'Publication' : 'Commentaire',
            id: String(id),
            author: author || 'Anonyme',
            content: content || '',
            original: originalContent || '',
            time: new Date().toLocaleTimeString(),
            timestamp: Date.now()
        };

        // Get existing modifications from localStorage
        const existingMods = JSON.parse(localStorage.getItem('ecotrack_modifications') || '[]');

        // Add new modification
        existingMods.push(modification);

        // Keep only last 100 modifications to avoid localStorage overflow
        const trimmedMods = existingMods.slice(-100);

        // Store back in localStorage
        localStorage.setItem('ecotrack_modifications', JSON.stringify(trimmedMods));

        console.log('Modification recorded for backoffice:', modification);
    } catch (err) {
        console.error('Error recording modification for backoffice:', err);
    }
}

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

/**
 * Validates author name to prevent special characters
 * @param {string} authorName - The author name to validate
 * @returns {Object} - { valid: boolean, error: string } - Validation result
 */
function validateAuthorName(authorName) {
    // Allow empty names (optional field) but validate if provided
    if (!authorName || !authorName.trim()) {
        return { valid: true, error: '' }; // Empty is allowed (optional field)
    }

    // Define forbidden special characters: . > ? ! and other potentially problematic characters
    const forbiddenChars = /[.>?!<>{}[\]\\|`~@#$%^&*()+=\/;:"'`]/;

    if (forbiddenChars.test(authorName)) {
        return {
            valid: false,
            error: 'Le nom d\'auteur ne peut pas contenir de caractères spéciaux comme . > ? ! < > { } [ ] \\ | ` ~ @ # $ % ^ & * ( ) + = / ; : " \' ou d\'autres caractères spéciaux.'
        };
    }

    // Check length (reasonable limit)
    if (authorName.length > 100) {
        return { valid: false, error: 'Le nom d\'auteur ne peut pas dépasser 100 caractères.' };
    }

    return { valid: true, error: '' };
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
                        <button class="report-post btn btn-warning" data-id="${item.id}" data-type="post" aria-label="Signaler la publication" style="background:#f59e0b;color:white;">Signaler</button>
                    </div>
                </div>
            </div>
            <div class="comments-section" data-post-id="${item.id}" style="display:none;margin-top:16px;padding-top:16px;border-top:1px solid #e6e6e6;">
                <h4 style="margin:0 0 12px 0;color:#2b3b36;">Commentaires (${commentCount})</h4>
                <div class="comments-display" style="margin-bottom:12px;"></div>
            </div>
            <div class="comments-zone" style="margin-top:10px;padding-top:10px;border-top:1px solid #eee;">
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input class="comment-author" placeholder="Votre nom (optionnel)" style="width:160px;padding:8px;border:1px solid #ddd;border-radius:6px;" title="Caractères spéciaux interdits: . > ? ! etc." />
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

    // Wire error modal close handlers
    const closeErrorModalBtn = document.getElementById('closeErrorModal');
    const errorModal = document.getElementById('errorModal');

    if (closeErrorModalBtn) {
        closeErrorModalBtn.addEventListener('click', closeErrorModal);
    }

    if (errorModal) {
        // Close on backdrop click
        errorModal.addEventListener('click', function (e) {
            if (e.target === errorModal) {
                closeErrorModal();
            }
        });
    }


    // ============================================
    // Report modal functionality
    // ============================================
    const reportModal = document.getElementById('reportModal');
    const closeReportModal = document.getElementById('closeReportModal');
    const submitReportBtn = document.getElementById('submitReport');
    const cancelReport = document.getElementById('cancelReport');
    const reportReason = document.getElementById('reportReason');
    const reporterName = document.getElementById('reporterName');

    let reportingContent = { type: '', id: 0 }; // Track what is being reported

    // Function to open report modal
    window.openReportModal = function (contentType, contentId) {
        reportingContent = { type: contentType, id: contentId };
        reportModal.style.display = 'flex';
        reportModal.classList.add('open');
        reportModal.setAttribute('aria-hidden', 'false');
        if (reportReason) reportReason.selectedIndex = 0;
        if (reporterName) reporterName.value = '';
    };

    // Function to close report modal
    function closeReportModalFn() {
        if (!reportModal) return;
        reportModal.classList.remove('open');
        setTimeout(() => {
            reportModal.style.display = 'none';
            reportModal.setAttribute('aria-hidden', 'true');
        }, 260);
        reportingContent = { type: '', id: 0 };
    }

    // Close button handler
    if (closeReportModal) {
        closeReportModal.addEventListener('click', closeReportModalFn);
    }

    // Cancel button handler
    if (cancelReport) {
        cancelReport.addEventListener('click', closeReportModalFn);
    }

    // Close on backdrop click
    if (reportModal) {
        reportModal.addEventListener('click', function (e) {
            if (e.target === reportModal) {
                closeReportModalFn();
            }
        });
    }

    // Submit report handler
    if (submitReportBtn) {
        submitReportBtn.addEventListener('click', function () {
            const reason = reportReason ? reportReason.value : 'other';
            const reporter = reporterName && reporterName.value.trim() ? reporterName.value.trim() : 'Anonyme';
            const REPORT_API_URL = new URL('../../controller/reportController.php', window.location.href).href;

            if (!reportingContent.type || !reportingContent.id) {
                showErrorModal('Erreur: Contenu non identifié pour signalement.');
                return;
            }

            submitReportBtn.disabled = true;
            const originalText = submitReportBtn.textContent;
            submitReportBtn.textContent = 'Envoi...';

            const formData = new FormData();
            formData.append('content_type', reportingContent.type);
            formData.append('content_id', reportingContent.id);
            formData.append('reason', reason);
            formData.append('reported_by', reporter);

            fetch(REPORT_API_URL, {
                method: 'POST',
                body: formData
            })
                .then(parseJsonSafe)
                .then(data => {
                    if (data && data.success) {
                        closeReportModalFn();
                        // Show success message
                        const successMsg = document.createElement('div');
                        successMsg.className = 'publish-success show';
                        successMsg.innerHTML = '<div class="check">✓</div><div style="margin-top:12px;color:#2f9b4a;font-weight:600;">Signalement envoyé!</div>';
                        successMsg.style.position = 'fixed';
                        successMsg.style.inset = '0';
                        successMsg.style.display = 'flex';
                        successMsg.style.flexDirection = 'column';
                        successMsg.style.alignItems = 'center';
                        successMsg.style.justifyContent = 'center';
                        successMsg.style.background = 'rgba(255, 255, 255, 0.95)';
                        successMsg.style.zIndex = '11000';
                        successMsg.style.borderRadius = '12px';
                        document.body.appendChild(successMsg);
                        setTimeout(() => successMsg.remove(), 2000);
                    } else {
                        showErrorModal('Erreur lors de l\'envoi du signalement: ' + (data && data.error ? data.error : ''));
                    }
                })
                .catch(err => {
                    console.error('Report submission error', err);
                    showErrorModal('Impossible d\'envoyer le signalement.');
                })
                .finally(() => {
                    submitReportBtn.disabled = false;
                    submitReportBtn.textContent = originalText;
                });
        });
    }


    // Add real-time validation for author name input
    if (postAuthor) {
        const postAuthorError = document.getElementById('postAuthorError');
        postAuthor.addEventListener('input', function () {
            const authorValue = this.value;
            const validation = validateAuthorName(authorValue);

            if (authorValue.trim() && !validation.valid) {
                this.style.border = '2px solid #D9534F';
                this.style.backgroundColor = '#fff5f5';
                if (postAuthorError) {
                    postAuthorError.textContent = validation.error;
                    postAuthorError.style.display = 'block';
                }
            } else {
                this.style.border = '';
                this.style.backgroundColor = '';
                if (postAuthorError) {
                    postAuthorError.style.display = 'none';
                }
            }
        });

        postAuthor.addEventListener('blur', function () {
            const authorValue = this.value;
            const validation = validateAuthorName(authorValue);

            if (authorValue.trim() && !validation.valid) {
                this.style.border = '2px solid #D9534F';
                this.style.backgroundColor = '#fff5f5';
                if (postAuthorError) {
                    postAuthorError.textContent = validation.error;
                    postAuthorError.style.display = 'block';
                }
            } else {
                this.style.border = '';
                this.style.backgroundColor = '';
                if (postAuthorError) {
                    postAuthorError.style.display = 'none';
                }
            }
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

            // Validate content
            if (content.length === 0) {
                showErrorModal('Veuillez écrire quelque chose pour publier.');
                return;
            }

            // Validate author name if provided
            if (sendBy && sendBy !== 'Anonyme') {
                const authorValidation = validateAuthorName(sendBy);
                if (!authorValidation.valid) {
                    showErrorModal(authorValidation.error);
                    const postAuthorError = document.getElementById('postAuthorError');
                    if (postAuthorError && postAuthor) {
                        postAuthorError.textContent = authorValidation.error;
                        postAuthorError.style.display = 'block';
                        postAuthor.style.border = '2px solid #D9534F';
                        postAuthor.style.backgroundColor = '#fff5f5';
                        setTimeout(() => {
                            postAuthorError.style.display = 'none';
                            postAuthor.style.border = '';
                            postAuthor.style.backgroundColor = '';
                        }, 5000);
                        postAuthor.focus();
                    }
                    return;
                }
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
                        // Record modification for backoffice stats
                        const originalContent = postContent.getAttribute('data-original-content') || '';
                        const originalAuthor = postAuthor.getAttribute('data-original-author') || '';
                        recordModificationForBackoffice('post', editingPostId, sendBy, content, originalContent);

                        editingPostId = null; editingCommentId = null;
                        if (modal) {
                            modal.classList.remove('open');
                            modal.setAttribute('aria-hidden', 'true');
                            setTimeout(() => { modal.style.display = 'none'; }, 260);
                        }
                        postContent.value = ''; postAuthor.value = '';
                        postContent.removeAttribute('data-original-content');
                        postAuthor.removeAttribute('data-original-author');
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
                    // Organize comments into a map for easy nesting
                    const commentMap = {};
                    const rootComments = [];

                    post.comments.forEach(c => {
                        c.replies = [];
                        commentMap[c.id] = c;
                    });

                    post.comments.forEach(c => {
                        if (c.reply_to_id && commentMap[c.reply_to_id]) {
                            commentMap[c.reply_to_id].replies.push(c);
                        } else {
                            rootComments.push(c);
                        }
                    });

                    // Recursive function to render comments
                    function renderCommentTree(comment, level = 0) {
                        const commentDiv = document.createElement('div');
                        commentDiv.className = 'comment-item';
                        if (level > 0) {
                            commentDiv.classList.add('nested');
                            commentDiv.style.marginLeft = (level * 20) + 'px';
                        }
                        commentDiv.dataset.commentId = comment.id;

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
                                    <div class="comment-author-label">${author}</div>
                                    <div class="comment-content">${body}</div>
                                    ${commentAttachmentHTML}
                                    <div style="margin-top:8px;">
                                        <button class="reply-comment-btn" data-comment-id="${comment.id}" data-post-id="${postId}">Répondre</button>
                                    </div>
                                    <div class="reply-form-container" id="reply-form-${comment.id}" style="display:none;margin-top:10px;">
                                        <!-- Reply form will be injected here -->
                                    </div>
                                </div>
                                <div style="flex:0 0 auto;display:flex;gap:6px;">
                                    <button class="edit-comment btn btn-sm btn-primary" data-comment-id="${comment.id}" style="padding:6px 8px;border-radius:4px;font-size:0.85em;">Modifier</button>
                                    <button class="delete-comment btn btn-sm btn-danger" data-comment-id="${comment.id}" style="padding:6px 8px;border-radius:4px;font-size:0.85em;">Supprimer</button>
                                    <button class="report-comment btn btn-sm btn-warning" data-id="${comment.id}" data-type="comment" style="padding:6px 8px;border-radius:4px;font-size:0.85em;background:#f59e0b;color:white;">Signaler</button>
                                </div>
                            </div>
                        `;
                        commentsDisplay.appendChild(commentDiv);

                        // Render replies
                        if (comment.replies && comment.replies.length > 0) {
                            comment.replies.forEach(reply => renderCommentTree(reply, level + 1));
                        }
                    }

                    rootComments.forEach(c => renderCommentTree(c));
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

    // Handle report post button
    const reportPostBtn = e.target.closest('.report-post');
    if (reportPostBtn) {
        const postId = reportPostBtn.getAttribute('data-id');
        const contentType = reportPostBtn.getAttribute('data-type');
        if (postId && contentType) {
            window.openReportModal(contentType, postId);
        }
        return;
    }

    // Handle report comment button
    const reportCommentBtn = e.target.closest('.report-comment');
    if (reportCommentBtn) {
        const commentId = reportCommentBtn.getAttribute('data-id');
        const contentType = reportCommentBtn.getAttribute('data-type');
        if (commentId && contentType) {
            window.openReportModal(contentType, commentId);
        }
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
        if (text.length === 0) { showErrorModal('Écris un commentaire avant de répondre.'); return; }

        // Validate author name if provided
        if (author && author !== 'Anonyme') {
            const authorValidation = validateAuthorName(author);
            if (!authorValidation.valid) {
                showErrorModal(authorValidation.error);
                if (authorInput) {
                    authorInput.style.border = '2px solid #D9534F';
                    authorInput.style.backgroundColor = '#fff5f5';
                    setTimeout(() => {
                        authorInput.style.border = '';
                        authorInput.style.backgroundColor = '';
                    }, 3000);
                    authorInput.focus();
                }
                return;
            }
        }

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
            const originalContent = post.contenu || '';
            const originalAuthor = post.send_by || '';
            document.getElementById('postAuthor').value = originalAuthor;
            document.getElementById('postContent').value = originalContent;
            // Store original content in data attributes for later use
            document.getElementById('postContent').setAttribute('data-original-content', originalContent);
            document.getElementById('postAuthor').setAttribute('data-original-author', originalAuthor);
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


    // Reply to a comment (Show form)
    const clickedReplyBtn = e.target.closest('.reply-comment-btn');
    if (clickedReplyBtn) {
        e.preventDefault();
        const commentId = clickedReplyBtn.getAttribute('data-comment-id');
        const postId = clickedReplyBtn.getAttribute('data-post-id');
        const formContainer = document.getElementById(`reply-form-${commentId}`);

        if (formContainer.style.display === 'block') {
            formContainer.style.display = 'none';
            return;
        }

        // Inject form if empty or only contains comments
        if (!formContainer.querySelector('.reply-box')) {
            formContainer.innerHTML = `
                <div class="reply-box">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <input class="reply-author" placeholder="Votre nom" />
                        <input class="reply-input" placeholder="Votre réponse..." />
                        <button class="submit-reply-btn btn btn-sm btn-primary" data-parent-comment-id="${commentId}" data-post-id="${postId}">Envoyer</button>
                    </div>
                </div>
            `;
        }
        formContainer.style.display = 'block';
        formContainer.querySelector('.reply-input').focus();
    }

    // Submit reply
    const clickedSubmitReply = e.target.closest('.submit-reply-btn');
    if (clickedSubmitReply) {
        const parentCommentId = clickedSubmitReply.getAttribute('data-parent-comment-id');
        const postId = clickedSubmitReply.getAttribute('data-post-id');
        const container = clickedSubmitReply.closest('.reply-box');
        const input = container.querySelector('.reply-input');
        const authorInput = container.querySelector('.reply-author');

        const text = input.value.trim();
        const author = (authorInput && authorInput.value.trim()) || 'Anonyme';

        if (text.length === 0) { showErrorModal('Veuillez écrire une réponse.'); return; }

        // Validate author name if provided
        if (author && author !== 'Anonyme') {
            const authorValidation = validateAuthorName(author);
            if (!authorValidation.valid) {
                showErrorModal(authorValidation.error);
                if (authorInput) {
                    authorInput.style.border = '2px solid #D9534F';
                    authorInput.style.backgroundColor = '#fff5f5';
                    setTimeout(() => {
                        authorInput.style.border = '';
                        authorInput.style.backgroundColor = '';
                    }, 3000);
                    authorInput.focus();
                }
                return;
            }
        }

        const formData = new FormData();
        formData.append('parent_id', postId); // The post ID is still the main parent
        formData.append('reply_to_id', parentCommentId); // The comment we are replying to
        formData.append('contenu', text);
        formData.append('send_by', author);

        clickedSubmitReply.disabled = true;
        fetch(API_URL, { method: 'POST', body: formData })
            .then(parseJsonSafe)
            .then(data => {
                if (data && data.success) {
                    // Refresh comments for this post
                    toggleCommentsForPost(postId);
                    // Force refresh if it was already open (toggle closes it, so we might need to call it twice or just re-fetch)
                    // Actually toggleCommentsForPost toggles visibility. If we want to refresh, we should just call the fetch logic again.
                    // But toggleCommentsForPost is simple: if visible, it hides. 
                    // Let's just hide and show again to refresh, or better: manually trigger a refresh.
                    // For now, let's just re-open it.
                    const commentsSection = document.querySelector(`.comments-section[data-post-id="${postId}"]`);
                    if (commentsSection) commentsSection.style.display = 'none'; // force close
                    setTimeout(() => toggleCommentsForPost(postId), 50); // re-open
                } else {
                    alert('Erreur lors de la réponse: ' + (data && data.error ? data.error : ''));
                    clickedSubmitReply.disabled = false;
                }
            })
            .catch(err => {
                console.error('Reply submit error', err);
                alert('Impossible d\'envoyer la réponse.');
                clickedSubmitReply.disabled = false;
            });
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
            const originalContent = contentEl.textContent || '';
            const author = authorEl ? authorEl.textContent : 'Anonyme';
            console.log('Updating comment', { commentId, newContent });
            // send update to backend with comment id
            fetch(API_URL, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `id=${encodeURIComponent(commentId)}&contenu=${encodeURIComponent(newContent)}` })
                .then(parseJsonSafe)
                .then(data => {
                    if (data && data.success) {
                        // Record modification for backoffice stats
                        recordModificationForBackoffice('comment', commentId, author, newContent, originalContent);

                        // Refresh comments
                        const postId = commentEl.closest('.comments-section').getAttribute('data-post-id');
                        const commentsSection = document.querySelector(`.comments-section[data-post-id="${postId}"]`);
                        if (commentsSection) commentsSection.style.display = 'none';
                        setTimeout(() => toggleCommentsForPost(postId), 50);
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
                if (data && data.success) {
                    // Refresh comments
                    const postId = commentEl.closest('.comments-section').getAttribute('data-post-id');
                    const commentsSection = document.querySelector(`.comments-section[data-post-id="${postId}"]`);
                    if (commentsSection) commentsSection.style.display = 'none';
                    setTimeout(() => toggleCommentsForPost(postId), 50);
                } else { alert('Erreur lors de la suppression: ' + (data && data.error ? data.error : '')); }
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

// ============================================
// Chatbot Logic
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    const chatbotBtn = document.getElementById('chatbotBtn');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const closeChatbot = document.getElementById('closeChatbot');
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');

    // Toggle Chatbot Window
    if (chatbotBtn && chatbotWindow) {
        chatbotBtn.addEventListener('click', () => {
            const isVisible = chatbotWindow.style.display !== 'none';
            chatbotWindow.style.display = isVisible ? 'none' : 'flex';
            if (!isVisible) {
                // Focus input when opening
                setTimeout(() => chatbotInput.focus(), 100);
            }
        });
    }

    // Close Chatbot
    if (closeChatbot) {
        closeChatbot.addEventListener('click', () => {
            chatbotWindow.style.display = 'none';
        });
    }

    // AI-Powered Send Logic
    function sendMessage() {
        const text = chatbotInput.value.trim();
        if (!text) return;

        addMessage(text, 'user');
        chatbotInput.value = '';

        // Show typing indicator
        const typingId = showTypingIndicator();
        const AI_API_URL = new URL('../../controller/aiController.php', window.location.href).href;

        // Call Backend API
        fetch(AI_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        })
            .then(res => res.json())
            .then(data => {
                removeTypingIndicator(typingId);

                if (data.success && data.reply) {
                    // Success: AI Reply
                    addMessage(data.reply, 'bot');
                } else if (data.fallback) {
                    // Fallback needed (API key missing or error)
                    console.warn('AI API Error (using fallback):', data.error);
                    const botResponse = getBotResponse(text); // Use local logic
                    addMessage(botResponse, 'bot');
                } else {
                    // Unknown error
                    addMessage("Je rencontre un petit problème technique, mais je suis toujours là !", 'bot');
                }
            })
            .catch(err => {
                console.error('Network Error:', err);
                removeTypingIndicator(typingId);
                // Network fallback
                const botResponse = getBotResponse(text);
                addMessage(botResponse, 'bot');
            });
    }

    function showTypingIndicator() {
        const id = 'typing-' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'typing-indicator';
        div.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
        chatbotMessages.appendChild(div);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        return id;
    }

    function removeTypingIndicator(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    // Event Listeners for Sending
    if (sendMessageBtn) {
        sendMessageBtn.addEventListener('click', sendMessage);
    }

    if (chatbotInput) {
        chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    // Add Message to UI
    function addMessage(text, sender) {
        const div = document.createElement('div');
        div.classList.add('message');
        div.classList.add(sender === 'user' ? 'user-message' : 'bot-message');
        div.textContent = text;
        chatbotMessages.appendChild(div);
        // Scroll to bottom
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    // Simple Mock Bot Logic
    function getBotResponse(input) {
        const lowerInput = input.toLowerCase();

        if (lowerInput.includes('bonjour') || lowerInput.includes('salut') || lowerInput.includes('hello')) {
            return "Bonjour ! Comment puis-je vous aider à devenir plus écolo aujourd'hui ?";
        }
        if (lowerInput.includes('règle') || lowerInput.includes('charte')) {
            return "Notre charte repose sur le respect, la bienveillance et le partage d'idées constructives pour l'environnement.";
        }
        if (lowerInput.includes('post') || lowerInput.includes('publier')) {
            return "Pour publier un post, cliquez sur le bouton 'Ajouter un post' en haut du mur communautaire. Vous pouvez même ajouter une photo !";
        }
        if (lowerInput.includes('contact') || lowerInput.includes('admin')) {
            return "Vous pouvez contacter les administrateurs via le formulaire de contact en bas de page ou signaler un contenu abusif directement.";
        }
        if (lowerInput.includes('merci')) {
            return "Avec plaisir ! N'hésitez pas si vous avez d'autres questions.";
        }

        // Default responses
        const defaults = [
            "Je ne suis pas sûr de comprendre, mais je suis là pour encourager vos initiatives vertes !",
            "C'est intéressant ! Dites-m'en plus.",
            "Pouvez-vous reformuler votre question ?",
            "N'oubliez pas : chaque petit geste compte pour la planète 🌍"
        ];
        return defaults[Math.floor(Math.random() * defaults.length)];
    }

    // ============================================
    // Advanced Chatbot Upgrade
    // ============================================

    const knowledgeBase = {
        greetings: {
            keywords: ['bonjour', 'salut', 'hello', 'coucou', 'hey', 'yo', 'ça va'],
            answers: [
                "Bonjour ! Je suis l'assistant EcoTrack. Comment puis-je vous aider à réduire votre empreinte carbone ?",
                "Salut ! Prêt à agir pour la planète aujourd'hui ?",
                "Hello ! Je suis là pour répondre à toutes vos questions sur l'écologie et notre communauté."
            ]
        },
        rules: {
            keywords: ['règle', 'charte', 'loi', 'interdit', 'comportement', 'insulte'],
            answers: [
                "Notre charte est simple : respect, bienveillance et écologie. Tout contenu haineux ou publicitaire sera supprimé.",
                "Pour garder cet espace sain, nous demandons à chacun de rester poli, constructif et bienveillant."
            ]
        },
        posting: {
            keywords: ['post', 'publier', 'écrire', 'ajout', 'photo', 'créer'],
            answers: [
                "Pour publier, cliquez sur le bouton 'Ajouter un post' en haut du mur. Vous pouvez ajouter du texte et même une image !",
                "Envie de partager ? Utilisez le bouton 'Ajouter un post'. C'est le meilleur moyen de faire entendre votre voix."
            ]
        },
        contact: {
            keywords: ['contact', 'admin', 'modérateur', 'support', 'aide', 'problème'],
            answers: [
                "Vous pouvez contacter l'équipe via le formulaire en bas de page ou signaler directement un contenu problématique.",
                "Besoin d'aide ? Les administrateurs sont à votre écoute. Signalez tout problème via les boutons 'Signaler' sur les posts."
            ]
        },
        thanks: {
            keywords: ['merci', 'top', 'super', 'cool', 'génial', 'thx'],
            answers: [
                "Avec grand plaisir ! 🌱",
                "Heureux de pouvoir aider ! Ensemble, on va plus loin.",
                "N'hésitez pas si vous avez d'autres questions. Je suis là pour ça !"
            ]
        },
        ecology: {
            keywords: ['écologie', 'bio', 'nature', 'vert', 'pollution', 'climat', 'planète', 'déchet', 'recyclage'],
            answers: [
                "L'écologie est au cœur de notre communauté. Avez-vous une astuce zéro déchet à partager ?",
                "Chaque geste compte. Ici, on partage des idées concrètes pour un avenir plus vert.",
                "Le saviez-vous ? Le recyclage d'une seule canette économise 95% de l'énergie nécessaire pour en fabriquer une nouvelle."
            ]
        }
    };

    // Override the previous getBotResponse function
    getBotResponse = function (input) {
        const lowerInput = input.toLowerCase();

        let bestMatch = null;
        let maxScore = 0;

        for (const [category, data] of Object.entries(knowledgeBase)) {
            let score = 0;
            data.keywords.forEach(word => {
                if (lowerInput.includes(word)) score++;
            });
            if (score > maxScore) {
                maxScore = score;
                bestMatch = data;
            }
        }

        if (bestMatch && maxScore > 0) {
            return bestMatch.answers[Math.floor(Math.random() * bestMatch.answers.length)];
        }

        const defaults = [
            "Je suis en train d'apprendre sur ce sujet. Pouvez-vous reformuler votre question ?",
            "C'est un point intéressant. Dites-m'en plus à ce propos.",
            "Je suis là pour vous guider sur EcoTrack. Avez-vous une question spécifique sur le forum ou l'écologie ?",
            "N'oubliez pas : chaque petit geste compte pour la planète 🌍. Comment puis-je vous aider autrement ?"
        ];
        return defaults[Math.floor(Math.random() * defaults.length)];
    };
});
