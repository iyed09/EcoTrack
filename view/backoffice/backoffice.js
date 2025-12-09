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
let processedModificationTimestamps = new Set(); // Track processed modifications to avoid duplicates

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

/**
 * Synchronizes modifications from frontoffice stored in localStorage
 * Reads modifications made in frontoffice and updates backoffice stats
 */
function syncModificationsFromFrontoffice() {
    try {
        const storedMods = JSON.parse(localStorage.getItem('ecotrack_modifications') || '[]');
        if (!Array.isArray(storedMods) || storedMods.length === 0) {
            return; // No modifications to sync
        }

        let newModsCount = 0;
        const processedTimestamps = [];

        storedMods.forEach(mod => {
            // Check if this modification was already processed
            if (mod.timestamp && processedModificationTimestamps.has(mod.timestamp)) {
                return; // Skip already processed modifications
            }

            // Add to modified items with source indicator
            modifiedItems.push({
                type: mod.type || 'Publication',
                id: mod.id,
                author: mod.author || 'Anonyme',
                content: mod.content || '',
                original: mod.original || '',
                time: mod.time || new Date().toLocaleTimeString(),
                source: 'frontoffice' // Mark as coming from frontoffice
            });

            // Mark as processed
            if (mod.timestamp) {
                processedModificationTimestamps.add(mod.timestamp);
                processedTimestamps.push(mod.timestamp);
            }

            // Increment edit count
            editCount++;
            newModsCount++;
        });

        // Remove processed modifications from localStorage (keep unprocessed ones)
        if (processedTimestamps.length > 0) {
            const remainingMods = storedMods.filter(mod =>
                !mod.timestamp || !processedTimestamps.includes(mod.timestamp)
            );
            localStorage.setItem('ecotrack_modifications', JSON.stringify(remainingMods));
        }

        // Update dashboard if there were new modifications
        if (newModsCount > 0) {
            updateDashboard();
            console.log(`Synchronized ${newModsCount} modification(s) from frontoffice`);
        }
    } catch (err) {
        console.error('Error syncing modifications from frontoffice:', err);
    }
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

/**
 * Validates author name to prevent special characters
 * @param {string} authorName - The author name to validate
 * @returns {Object} - { valid: boolean, error: string } - Validation result
 */
function validateAuthorName(authorName) {
    if (!authorName || !authorName.trim()) {
        return { valid: false, error: 'Le nom d\'auteur ne peut pas être vide.' };
    }

    // Define forbidden special characters: . > ? ! and other potentially problematic characters
    const forbiddenChars = /[.>?!<>{}[\]\\|`~@#$%^&*()+=\/;:"'`]/;

    if (forbiddenChars.test(authorName)) {
        return {
            valid: false,
            error: 'Le nom d\'auteur ne peut pas contenir de caractères spéciaux comme . > ? ! < > { } [ ] \\ | ` ~ @ # $ % ^ & * ( ) + = / ; : " \' ou d\'autres caractères spéciaux.'
        };
    }

    // Check for only whitespace
    if (!authorName.trim()) {
        return { valid: false, error: 'Le nom d\'auteur ne peut pas être vide.' };
    }

    // Check length (reasonable limit)
    if (authorName.length > 100) {
        return { valid: false, error: 'Le nom d\'auteur ne peut pas dépasser 100 caractères.' };
    }

    return { valid: true, error: '' };
}

/**
 * Resets the author field validation state
 */
function resetAuthorValidation() {
    const modalAuthor = document.getElementById('modalAuthor');
    const modalAuthorError = document.getElementById('modalAuthorError');
    if (modalAuthor) {
        modalAuthor.style.border = '1px solid #ddd';
        modalAuthor.style.backgroundColor = '';
    }
    if (modalAuthorError) {
        modalAuthorError.style.display = 'none';
        modalAuthorError.textContent = '';
    }
}

/**
 * Displays existing file attachment in the modal
 * @param {string} attachmentPath - The path to the existing attachment
 */
function displayExistingFile(attachmentPath) {
    if (!attachmentPath) return;

    const fileUploadLabel = document.getElementById('fileUploadLabel');
    const fileUploadText = document.getElementById('fileUploadText');
    const fileUploadIcon = document.getElementById('fileUploadIcon');
    const filePreview = document.getElementById('modalFilePreview');
    const fileName = document.getElementById('modalFileName');

    if (fileUploadLabel && fileUploadText && fileUploadIcon && filePreview && fileName) {
        const fileNameOnly = attachmentPath.split('/').pop();
        fileUploadText.textContent = `Fichier actuel: ${fileNameOnly}`;
        fileUploadLabel.classList.add('file-selected');
        fileUploadIcon.textContent = '📎';

        fileName.textContent = fileNameOnly;
        filePreview.style.display = 'block';
    }
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

        // Build attachment HTML if present
        let attachmentHTML = '';
        if (post.attachment) {
            const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(post.attachment);
            if (isImage) {
                attachmentHTML = `<div style="margin-top:8px;"><img src="../../${escapeHtml(post.attachment)}" style="max-width:300px;max-height:300px;border-radius:6px;border:1px solid #ddd;" alt="Attachment" /></div>`;
            } else {
                const fileName = post.attachment.split('/').pop();
                attachmentHTML = `<div style="margin-top:8px;"><a href="../../${escapeHtml(post.attachment)}" target="_blank" style="color:#357a38;text-decoration:none;">📎 ${escapeHtml(fileName)}</a></div>`;
            }
        }

        postEl.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <div style="flex:1">
                    <div style="font-weight:700;color:#2b3b36;margin-bottom:6px;">${author}</div>
                    <div class="post-text" style="color:#222;">${body}</div>
                    ${attachmentHTML}
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
        <div style="margin-bottom:8px;">
            <label for="modalAuthor" style="display:block;font-weight:600;margin-bottom:6px;">Auteur</label>
            <input id="modalAuthor" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;" placeholder="Nom d'auteur (caractères spéciaux interdits: . > ? ! etc.)"/>
            <div id="modalAuthorError" style="display:none;color:#D9534F;font-size:0.85em;margin-top:4px;font-weight:500;"></div>
        </div>
        <div id="modalTime" style="color:#666;margin-bottom:12px"></div>
        <div style="margin-bottom:8px;position:relative;"><label for="modalContent" style="display:block;font-weight:600;margin-bottom:6px;">Contenu</label><textarea id="modalContent" style="width:100%;min-height:120px;padding:8px;border:1px solid #ddd;border-radius:6px;"></textarea><button type="button" class="emoji-picker-btn" id="modalEmojiBtn" style="position:absolute;right:12px;bottom:12px;padding:6px 10px;font-size:18px;" aria-label="Ajouter un emoji" title="Ajouter un emoji">😊</button><div class="emoji-picker" id="modalEmojiPicker" style="display:none;"></div></div>
        <div style="margin-bottom:8px;">
            <label style="display:block;font-weight:600;margin-bottom:8px;">📎 Fichier joint (optionnel)</label>
            <div style="position:relative;display:inline-block;width:100%;">
                <input id="modalAttachment" type="file" accept="image/*,.pdf,.doc,.docx,.txt" style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;"/>
                <label for="modalAttachment" id="fileUploadLabel" style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:14px 20px;background:linear-gradient(135deg, #60c072 0%, #357a38 100%);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:15px;transition:all 0.3s ease;box-shadow:0 4px 15px rgba(47, 155, 74, 0.3);position:relative;overflow:hidden;">
                    <span id="fileUploadIcon" style="font-size:20px;transition:transform 0.3s ease;">📤</span>
                    <span id="fileUploadText">Choisir un fichier</span>
                    <span id="fileUploadArrow" style="margin-left:auto;font-size:18px;transition:transform 0.3s ease;">→</span>
                </label>
            </div>
            <div id="modalFilePreview" style="margin-top:12px;display:none;padding:12px;background:linear-gradient(135deg, #f0f8f2 0%, #e6f2e8 100%);border-radius:8px;border:2px solid #60c072;animation:slideIn 0.3s ease;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:24px;">📎</span>
                        <span id="modalFileName" style="font-weight:600;color:#2b3b36;"></span>
                    </div>
                    <button id="removeFileBtn" style="background:#D9534F;color:#fff;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-size:14px;transition:all 0.2s ease;">✕ Supprimer</button>
                </div>
            </div>
        </div>
        <input type="hidden" id="modalOriginalContent" />
        <div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end;"><button id="modalSave" class="btn-primary">Enregistrer</button><button id="modalDelete" class="btn-danger">Supprimer</button></div>
    </div>`;
    document.body.appendChild(modal);
    document.getElementById('closeModal').addEventListener('click', closeModal);

    // Add CSS animations for file upload button
    if (!document.getElementById('fileUploadStyles')) {
        const style = document.createElement('style');
        style.id = 'fileUploadStyles';
        style.textContent = `
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.05);
                }
            }
            @keyframes bounce {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-5px);
                }
            }
            #fileUploadLabel:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(47, 155, 74, 0.4);
            }
            #fileUploadLabel:hover #fileUploadIcon {
                transform: rotate(15deg) scale(1.1);
            }
            #fileUploadLabel:hover #fileUploadArrow {
                transform: translateX(5px);
            }
            #fileUploadLabel:active {
                transform: translateY(0);
                box-shadow: 0 2px 10px rgba(47, 155, 74, 0.3);
            }
            #fileUploadLabel.file-selected {
                background: linear-gradient(135deg, #357a38 0%, #2b6f2e 100%);
            }
            #fileUploadLabel.file-selected #fileUploadIcon {
                animation: bounce 0.6s ease;
            }
            #removeFileBtn:hover {
                background: #c9302c !important;
                transform: scale(1.05);
            }
            #removeFileBtn:active {
                transform: scale(0.95);
            }
        `;
        document.head.appendChild(style);
    }

    // Add file upload button animations and handlers
    const fileInput = document.getElementById('modalAttachment');
    const fileUploadLabel = document.getElementById('fileUploadLabel');
    const fileUploadText = document.getElementById('fileUploadText');
    const fileUploadIcon = document.getElementById('fileUploadIcon');
    const filePreview = document.getElementById('modalFilePreview');
    const fileName = document.getElementById('modalFileName');
    const removeFileBtn = document.getElementById('removeFileBtn');

    if (fileInput && fileUploadLabel) {
        // Handle file selection
        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Update button text and style
                fileUploadText.textContent = `Fichier sélectionné: ${file.name}`;
                fileUploadLabel.classList.add('file-selected');
                fileUploadIcon.textContent = '✅';

                // Show preview
                fileName.textContent = file.name;
                filePreview.style.display = 'block';

                // Add pulse animation
                fileUploadLabel.style.animation = 'pulse 0.5s ease';
                setTimeout(() => {
                    fileUploadLabel.style.animation = '';
                }, 500);
            }
        });

        // Handle remove file button
        if (removeFileBtn) {
            removeFileBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                // Reset file input
                fileInput.value = '';

                // Reset button
                fileUploadText.textContent = 'Choisir un fichier';
                fileUploadLabel.classList.remove('file-selected');
                fileUploadIcon.textContent = '📤';

                // Hide preview
                filePreview.style.display = 'none';
            });
        }

        // Add hover effects with mouse events
        fileUploadLabel.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-2px)';
        });

        fileUploadLabel.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    }

    // Add real-time validation for author name input
    const modalAuthor = document.getElementById('modalAuthor');
    const modalAuthorError = document.getElementById('modalAuthorError');
    if (modalAuthor && modalAuthorError) {
        modalAuthor.addEventListener('input', function () {
            const authorValue = this.value;
            const validation = validateAuthorName(authorValue);

            if (authorValue.trim() && !validation.valid) {
                this.style.border = '2px solid #D9534F';
                this.style.backgroundColor = '#fff5f5';
                modalAuthorError.textContent = validation.error;
                modalAuthorError.style.display = 'block';
            } else {
                this.style.border = '1px solid #ddd';
                this.style.backgroundColor = '';
                modalAuthorError.style.display = 'none';
            }
        });

        modalAuthor.addEventListener('blur', function () {
            const authorValue = this.value;
            const validation = validateAuthorName(authorValue);

            if (authorValue.trim() && !validation.valid) {
                this.style.border = '2px solid #D9534F';
                this.style.backgroundColor = '#fff5f5';
                modalAuthorError.textContent = validation.error;
                modalAuthorError.style.display = 'block';
            } else {
                this.style.border = '1px solid #ddd';
                this.style.backgroundColor = '';
                modalAuthorError.style.display = 'none';
            }
        });
    }

    document.getElementById('modalSave').addEventListener('click', function () {
        const type = this.getAttribute('data-type');
        const contenu = document.getElementById('modalContent').value;
        const send_by = document.getElementById('modalAuthor').value || '';
        const originalContent = document.getElementById('modalOriginalContent').value || '';

        // Validate author name
        const authorValidation = validateAuthorName(send_by);
        if (!authorValidation.valid) {
            showBackofficeMessage(authorValidation.error, true);
            const modalAuthor = document.getElementById('modalAuthor');
            if (modalAuthor) {
                modalAuthor.style.border = '2px solid #D9534F';
                modalAuthor.style.backgroundColor = '#fff5f5';
                setTimeout(() => {
                    modalAuthor.style.border = '1px solid #ddd';
                    modalAuthor.style.backgroundColor = '';
                }, 3000);
                modalAuthor.focus();
            }
            return;
        }

        if (type === 'post') {
            const postId = this.getAttribute('data-post-id');
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('contenu', contenu);
            formData.append('send_by', send_by);
            // Add file if selected
            const fileInput = document.getElementById('modalAttachment');
            if (fileInput && fileInput.files.length > 0) {
                formData.append('attachment', fileInput.files[0]);
            }
            fetch('../../controller/communityController.php', {
                method: 'POST',
                body: formData
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
                        original: originalContent,
                        time: new Date().toLocaleTimeString(),
                        source: 'backoffice'
                    });
                    updateDashboard();
                } else { showBackofficeMessage('Erreur: ' + (data && data.error ? data.error : 'Réponse invalide'), true); }
            }).catch(err => { showBackofficeMessage('Erreur lors de la sauvegarde. Voir console. ' + (err.raw || err.message || ''), true); console.error(err); });
        } else if (type === 'comment') {
            const commentId = this.getAttribute('data-comment-id');
            const formData = new FormData();
            formData.append('id', commentId);
            formData.append('contenu', contenu);
            formData.append('send_by', send_by);
            // Add file if selected
            const fileInput = document.getElementById('modalAttachment');
            if (fileInput && fileInput.files.length > 0) {
                formData.append('attachment', fileInput.files[0]);
            }
            fetch('../../controller/communityController.php', {
                method: 'POST',
                body: formData
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
                        original: originalContent,
                        time: new Date().toLocaleTimeString(),
                        source: 'backoffice'
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
            const formData = new FormData();
            formData.append('parent_id', postId);
            formData.append('contenu', contenu);
            formData.append('send_by', send_by);
            // Add file if selected
            const fileInput = document.getElementById('modalAttachment');
            if (fileInput && fileInput.files.length > 0) {
                formData.append('attachment', fileInput.files[0]);
            }
            fetch('../../controller/communityController.php', {
                method: 'POST',
                body: formData
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

    // Reset validation state
    resetAuthorValidation();

    // Reset file upload button first
    const fileInput = document.getElementById('modalAttachment');
    const fileUploadLabel = document.getElementById('fileUploadLabel');
    const fileUploadText = document.getElementById('fileUploadText');
    const fileUploadIcon = document.getElementById('fileUploadIcon');
    const filePreview = document.getElementById('modalFilePreview');

    if (fileInput) fileInput.value = '';
    if (fileUploadLabel) {
        fileUploadLabel.classList.remove('file-selected');
        fileUploadLabel.style.transform = '';
        fileUploadLabel.style.animation = '';
    }
    if (fileUploadText) fileUploadText.textContent = 'Choisir un fichier';
    if (fileUploadIcon) fileUploadIcon.textContent = '📤';
    if (filePreview) filePreview.style.display = 'none';

    modalAuthor.value = post.send_by || 'Anonyme';
    modalTime.textContent = post.time || '';
    modalContent.value = post.contenu || '';
    document.getElementById('modalOriginalContent').value = post.contenu || ''; // Capture original

    // Display existing file if present
    if (post.attachment) {
        displayExistingFile(post.attachment);
    }

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

    // Reset validation state
    resetAuthorValidation();

    // Reset file upload button first
    const fileInput = document.getElementById('modalAttachment');
    const fileUploadLabel = document.getElementById('fileUploadLabel');
    const fileUploadText = document.getElementById('fileUploadText');
    const fileUploadIcon = document.getElementById('fileUploadIcon');
    const filePreview = document.getElementById('modalFilePreview');

    if (fileInput) fileInput.value = '';
    if (fileUploadLabel) {
        fileUploadLabel.classList.remove('file-selected');
        fileUploadLabel.style.transform = '';
        fileUploadLabel.style.animation = '';
    }
    if (fileUploadText) fileUploadText.textContent = 'Choisir un fichier';
    if (fileUploadIcon) fileUploadIcon.textContent = '📤';
    if (filePreview) filePreview.style.display = 'none';

    modalAuthor.value = c.send_by || 'Anonyme';
    modalTime.textContent = '';
    modalContent.value = c.contenu || '';
    document.getElementById('modalOriginalContent').value = c.contenu || ''; // Capture original

    // Display existing file if present
    if (c.attachment) {
        displayExistingFile(c.attachment);
    }

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
            // Organize comments into a tree structure
            const commentMap = {};
            const rootComments = [];

            // Initialize all comments with empty replies array
            post.comments.forEach(c => {
                c.replies = [];
                commentMap[c.id] = c;
            });

            // Build the tree structure
            post.comments.forEach(c => {
                if (c.reply_to_id && commentMap[c.reply_to_id]) {
                    commentMap[c.reply_to_id].replies.push(c);
                } else {
                    rootComments.push(c);
                }
            });

            // Function to render a single comment (root level or reply)
            function renderComment(comment, postId, isReply = false) {
                const commentDiv = document.createElement('div');
                commentDiv.style.background = isReply ? '#f0f8f2' : '#f9f9f9';
                commentDiv.style.padding = '10px';
                commentDiv.style.marginBottom = '8px';
                commentDiv.style.borderRadius = '6px';
                commentDiv.style.border = '1px solid #e0e0e0';
                if (isReply) {
                    commentDiv.style.marginLeft = '20px';
                    commentDiv.style.borderLeft = '3px solid #60c072';
                }
                commentDiv.dataset.commentId = comment.id;
                commentDiv.classList.add('comment-item');

                const author = escapeHtml(comment.send_by || 'Anonyme');
                const body = escapeHtml(comment.contenu || '');
                const replyCount = (comment.replies && comment.replies.length > 0) ? comment.replies.length : 0;

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

                // Build replies section HTML (hidden by default)
                let repliesSectionHTML = '';
                if (replyCount > 0) {
                    repliesSectionHTML = `
                        <div class="replies-section" data-comment-id="${comment.id}" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid #e0e0e0;">
                            <div class="replies-list" data-comment-id="${comment.id}"></div>
                        </div>
                    `;
                }

                commentDiv.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;">
                        <div style="flex:1">
                            <div style="font-weight:600;color:#2b3b36;margin-bottom:4px;">${author}</div>
                            <div style="color:#333;">${body}</div>
                            ${commentAttachmentHTML}
                            ${replyCount > 0 ? `<div style="margin-top:8px;"><button class="view-replies-btn" data-comment-id="${comment.id}" data-post-id="${postId}" style="background:#60c072;color:#fff;border:none;padding:6px 10px;border-radius:4px;font-size:0.85em;cursor:pointer;">💬 Voir ${replyCount} réponse(s)</button></div>` : ''}
                            ${repliesSectionHTML}
                        </div>
                        <div style="flex:0 0 auto;display:flex;gap:6px;">
                            <button class="edit-comment-btn" data-comment-id="${comment.id}" data-post-id="${postId}" style="background:#2b6f2e;color:#fff;border:none;padding:6px 8px;border-radius:4px;font-size:0.85em;">Modifier</button>
                            <button class="delete-comment-btn" data-comment-id="${comment.id}" style="background:#D9534F;color:#fff;border:none;padding:6px 8px;border-radius:4px;font-size:0.85em;">Supprimer</button>
                        </div>
                    </div>
                `;
                return commentDiv;
            }

            // Render all root comments (only top-level comments, not replies)
            rootComments.forEach(c => {
                const commentDiv = renderComment(c, postId, false);
                commentsList.appendChild(commentDiv);
            });
        }

        commentsSection.style.display = 'block';
    }
}

/**
 * Toggles the visibility of replies for a specific comment
 * @param {string|number} commentId - The ID of the comment whose replies to toggle
 * @param {string|number} postId - The ID of the post containing the comment
 */
function toggleRepliesForComment(commentId, postId) {
    const commentItem = document.querySelector(`.comment-item[data-comment-id="${commentId}"]`);
    if (!commentItem) return;

    const repliesSection = commentItem.querySelector(`.replies-section[data-comment-id="${commentId}"]`);
    const repliesList = commentItem.querySelector(`.replies-list[data-comment-id="${commentId}"]`);
    const viewRepliesBtn = commentItem.querySelector(`.view-replies-btn[data-comment-id="${commentId}"]`);

    if (!repliesSection || !repliesList) return;

    const isVisible = repliesSection.style.display !== 'none';

    if (isVisible) {
        repliesSection.style.display = 'none';
        if (viewRepliesBtn) {
            const post = posts.find(x => String(x.id) === String(postId));
            if (post) {
                const comment = findCommentInPost(post, commentId);
                if (comment && comment.replies) {
                    viewRepliesBtn.textContent = `💬 Voir ${comment.replies.length} réponse(s)`;
                }
            }
        }
    } else {
        // Find the comment and its replies
        const post = posts.find(x => String(x.id) === String(postId));
        if (!post) return;

        const comment = findCommentInPost(post, commentId);
        if (!comment || !comment.replies || comment.replies.length === 0) return;

        // Clear existing replies in the list
        repliesList.innerHTML = '';

        // Recursive function to render a reply and its nested structure
        function renderReply(reply, postId, parentElement) {
            const replyDiv = document.createElement('div');
            replyDiv.style.background = '#f0f8f2';
            replyDiv.style.padding = '10px';
            replyDiv.style.marginBottom = '8px';
            replyDiv.style.borderRadius = '6px';
            replyDiv.style.border = '1px solid #e0e0e0';
            replyDiv.style.borderLeft = '3px solid #60c072';
            replyDiv.style.marginLeft = '20px';
            replyDiv.dataset.commentId = reply.id;
            replyDiv.classList.add('comment-item');

            const author = escapeHtml(reply.send_by || 'Anonyme');
            const body = escapeHtml(reply.contenu || '');
            const replyCount = (reply.replies && reply.replies.length > 0) ? reply.replies.length : 0;

            // Build attachment HTML if present
            let replyAttachmentHTML = '';
            if (reply.attachment) {
                const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(reply.attachment);
                if (isImage) {
                    replyAttachmentHTML = `<div style="margin-top:6px;"><img src="../../${escapeHtml(reply.attachment)}" style="max-width:200px;max-height:200px;border-radius:4px;border:1px solid #ccc;" alt="Attachment" /></div>`;
                } else {
                    const fileName = reply.attachment.split('/').pop();
                    replyAttachmentHTML = `<div style="margin-top:6px;"><a href="../../${escapeHtml(reply.attachment)}" target="_blank" style="color:#357a38;text-decoration:none;font-size:0.9em;">📎 ${escapeHtml(fileName)}</a></div>`;
                }
            }

            // Build nested replies section if this reply has replies
            let nestedRepliesHTML = '';
            if (replyCount > 0) {
                nestedRepliesHTML = `
                    <div class="replies-section" data-comment-id="${reply.id}" style="display:none;margin-top:12px;padding-top:12px;border-top:1px solid #e0e0e0;">
                        <div class="replies-list" data-comment-id="${reply.id}"></div>
                    </div>
                `;
            }

            replyDiv.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;">
                    <div style="flex:1">
                        <div style="font-weight:600;color:#2b3b36;margin-bottom:4px;">${author}</div>
                        <div style="color:#333;">${body}</div>
                        ${replyAttachmentHTML}
                        ${replyCount > 0 ? `<div style="margin-top:8px;"><button class="view-replies-btn" data-comment-id="${reply.id}" data-post-id="${postId}" style="background:#60c072;color:#fff;border:none;padding:6px 10px;border-radius:4px;font-size:0.85em;cursor:pointer;">💬 Voir ${replyCount} réponse(s)</button></div>` : ''}
                        ${nestedRepliesHTML}
                    </div>
                    <div style="flex:0 0 auto;display:flex;gap:6px;">
                        <button class="edit-comment-btn" data-comment-id="${reply.id}" data-post-id="${postId}" style="background:#2b6f2e;color:#fff;border:none;padding:6px 8px;border-radius:4px;font-size:0.85em;">Modifier</button>
                        <button class="delete-comment-btn" data-comment-id="${reply.id}" style="background:#D9534F;color:#fff;border:none;padding:6px 8px;border-radius:4px;font-size:0.85em;">Supprimer</button>
                    </div>
                </div>
            `;
            parentElement.appendChild(replyDiv);
        }

        // Render each reply
        comment.replies.forEach(reply => {
            renderReply(reply, postId, repliesList);
        });

        repliesSection.style.display = 'block';
        if (viewRepliesBtn) {
            viewRepliesBtn.textContent = `🔽 Masquer les réponses`;
        }
    }
}

/**
 * Helper function to find a comment in a post's comment tree
 * @param {Object} post - The post object
 * @param {string|number} commentId - The ID of the comment to find
 * @returns {Object|null} - The comment object or null if not found
 */
function findCommentInPost(post, commentId) {
    if (!post.comments || !Array.isArray(post.comments)) return null;

    // First, build the comment tree
    const commentMap = {};
    post.comments.forEach(c => {
        c.replies = [];
        commentMap[c.id] = c;
    });

    post.comments.forEach(c => {
        if (c.reply_to_id && commentMap[c.reply_to_id]) {
            commentMap[c.reply_to_id].replies.push(c);
        }
    });

    // Recursive function to search for comment
    function searchComment(comments, targetId) {
        for (const comment of comments) {
            if (String(comment.id) === String(targetId)) {
                return comment;
            }
            if (comment.replies && comment.replies.length > 0) {
                const found = searchComment(comment.replies, targetId);
                if (found) return found;
            }
        }
        return null;
    }

    // Search in all comments (including nested ones)
    return searchComment(Object.values(commentMap), commentId);
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

    // Reset validation state
    resetAuthorValidation();

    // Reset file upload button
    const fileInput = document.getElementById('modalAttachment');
    const fileUploadLabel = document.getElementById('fileUploadLabel');
    const fileUploadText = document.getElementById('fileUploadText');
    const fileUploadIcon = document.getElementById('fileUploadIcon');
    const filePreview = document.getElementById('modalFilePreview');

    if (fileInput) fileInput.value = '';
    if (fileUploadLabel) {
        fileUploadLabel.classList.remove('file-selected');
        fileUploadLabel.style.transform = '';
        fileUploadLabel.style.animation = '';
    }
    if (fileUploadText) fileUploadText.textContent = 'Choisir un fichier';
    if (fileUploadIcon) fileUploadIcon.textContent = '📤';
    if (filePreview) filePreview.style.display = 'none';

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

    // Reset author field validation state
    resetAuthorValidation();

    // Reset file upload button state
    const fileInput = document.getElementById('modalAttachment');
    const fileUploadLabel = document.getElementById('fileUploadLabel');
    const fileUploadText = document.getElementById('fileUploadText');
    const fileUploadIcon = document.getElementById('fileUploadIcon');
    const filePreview = document.getElementById('modalFilePreview');

    if (fileInput) {
        fileInput.value = '';
    }
    if (fileUploadLabel) {
        fileUploadLabel.classList.remove('file-selected');
        fileUploadLabel.style.transform = '';
        fileUploadLabel.style.animation = '';
    }
    if (fileUploadText) {
        fileUploadText.textContent = 'Choisir un fichier';
    }
    if (fileUploadIcon) {
        fileUploadIcon.textContent = '📤';
    }
    if (filePreview) {
        filePreview.style.display = 'none';
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
                // Sync modifications from frontoffice after fetching posts
                syncModificationsFromFrontoffice();
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

    // Sync modifications from frontoffice
    syncModificationsFromFrontoffice();

    // Set up periodic sync every 5 seconds to catch modifications made in frontoffice
    setInterval(syncModificationsFromFrontoffice, 5000);

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

        // View replies for a comment
        if (e.target.classList && e.target.classList.contains('view-replies-btn')) {
            const commentId = e.target.getAttribute('data-comment-id');
            const postId = e.target.getAttribute('data-post-id');
            toggleRepliesForComment(commentId, postId);
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

// ============================================================================
// MODIFIED ITEMS DISPLAY
// ============================================================================

function showModifiedItemsModal() {
    let modal = document.getElementById('modifiedItemsModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modifiedItemsModal';
        modal.style.position = 'fixed';
        modal.style.left = '0';
        modal.style.top = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.display = 'none';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.background = 'rgba(0,0,0,0.45)';
        modal.style.zIndex = '2000';
        modal.innerHTML = `<div style="background:#fff;padding:20px;border-radius:8px;max-width:700px;width:90%;max-height:80vh;overflow-y:auto;box-shadow:0 8px 30px rgba(0,0,0,0.2);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <div>
                    <h3 style="margin:0;color:#2b3b36;">Modifications récentes</h3>
                    <p style="margin:4px 0 0 0;font-size:0.85em;color:#666;">Inclut les modifications du Frontoffice et du Backoffice</p>
                </div>
                <button id="closeModifiedModal" style="background:transparent;border:none;font-size:20px;cursor:pointer;">✕</button>
            </div>
            <div id="modifiedList"></div>
        </div>`;
        document.body.appendChild(modal);
        document.getElementById('closeModifiedModal').addEventListener('click', () => modal.style.display = 'none');
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
    }

    const list = document.getElementById('modifiedList');
    list.innerHTML = '';

    if (modifiedItems.length === 0) {
        list.innerHTML = '<div style="color:#666;font-style:italic;">Aucune modification dans cette session.</div>';
    } else {
        // Show newest first
        [...modifiedItems].reverse().forEach(item => {
            const div = document.createElement('div');
            div.style.borderBottom = '1px solid #eee';
            div.style.padding = '12px 0';

            const original = escapeHtml(item.original || '');
            const current = escapeHtml(item.content || '');

            const sourceBadge = item.source === 'frontoffice'
                ? '<span style="background:#60c072;color:#fff;padding:2px 8px;border-radius:12px;font-size:0.75em;margin-left:8px;">Frontoffice</span>'
                : '<span style="background:#357a38;color:#fff;padding:2px 8px;border-radius:12px;font-size:0.75em;margin-left:8px;">Backoffice</span>';

            div.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <div style="display:flex;align-items:center;">
                        <span style="font-weight:700;color:#2f9b4a;">${item.type} #${item.id}</span>
                        ${sourceBadge}
                    </div>
                    <span style="font-size:0.85em;color:#888;">${item.time}</span>
                </div>
                <div style="font-size:0.9em;color:#555;margin-bottom:8px;">Par: <strong>${escapeHtml(item.author)}</strong></div>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:0.8em;text-transform:uppercase;color:#d9534f;font-weight:700;margin-bottom:4px;">Avant</div>
                        <div style="background:#fff5f5;padding:8px;border-radius:4px;color:#333;font-size:0.95em;border:1px solid #ffcccc;min-height:40px;word-wrap:break-word;">${original || '<em style="color:#999;">Vide</em>'}</div>
                    </div>
                    <div>
                        <div style="font-size:0.8em;text-transform:uppercase;color:#357a38;font-weight:700;margin-bottom:4px;">Après</div>
                        <div style="background:#f7fff8;padding:8px;border-radius:4px;color:#333;font-size:0.95em;border:1px solid #e6f2e8;min-height:40px;word-wrap:break-word;">${current || '<em style="color:#999;">Vide</em>'}</div>
                    </div>
                </div>
            `;
            list.appendChild(div);
        });
    }

    modal.style.display = 'flex';
}

// ============================================================================
// REPORTS MANAGEMENT
// ============================================================================

const REPORT_API_URL = new URL('../../controller/reportController.php', window.location.href).href;
const reportsCard = document.getElementById('reportsCard');
const reportsSection = document.getElementById('reportsSection');
const reportsFeed = document.getElementById('reportsFeed');
const closeReportsBtn = document.getElementById('closeReports');
const dashboardView = document.querySelector('.dashboard-overview');
const mainFeed = document.querySelector('.table-card');

async function fetchReports() {
    try {
        const response = await fetch(REPORT_API_URL);
        const data = await parseJsonSafe(response);
        return Array.isArray(data) ? data : [];
    } catch (err) {
        console.error('Error fetching reports:', err);
        return [];
    }
}

function renderReports(reports) {
    if (!reportsFeed) return;

    const overviewReports = document.getElementById('overviewReports');
    if (overviewReports) {
        overviewReports.textContent = reports.length;
    }

    if (reports.length === 0) {
        reportsFeed.innerHTML = `<div style="text-align:center;padding:60px 20px;color:#666;"><span style="font-size:64px;">✅</span><h3 style="margin-top:16px;color:#1e7e34;">Aucun signalement en attente</h3></div>`;
        return;
    }

    reportsFeed.innerHTML = '';

    const reasonLabels = { spam: 'Spam', offensive: 'Contenu offensant', harassment: 'Harcèlement', misinformation: 'Désinformation', other: 'Autre' };

    reports.forEach(report => {
        const card = document.createElement('div');
        card.style.cssText = 'background:#fff;border:2px solid #f59e0b;border-radius:12px;padding:20px;box-shadow: 0 2px 8px rgba(245,158,11,0.1)';
        const icon = report.content_type === 'post' ? '📝' : '💬';
        const label = report.content_type === 'post' ? 'Publication' : 'Commentaire';
        card.innerHTML = `<div style="display:flex;gap:20px;"><div style="flex:1;"><div style="display:flex;gap:8px;margin-bottom:12px;"><span>${icon}</span><strong style="color:#1e7e34;">${label} signalé</strong><span style="background:#f59e0b;color:white;padding:4px 8px;border-radius:6px;font-size:0.75rem;">${escapeHtml(reasonLabels[report.reason] || report.reason)}</span></div><div style="background:#f9fafb;padding:12px;border-radius:8px;margin-bottom:12px;border-left:4px solid #e5e7eb;"><div style="color:#6b7280;font-size:0.85rem;">Contenu:</div><div style="color:#111827;">${escapeHtml(report.content || 'Contenu supprimé')}</div></div><div style="display:flex;gap:20px;font-size:0.85rem;color:#6b7280;"><div><strong>Auteur:</strong> ${escapeHtml(report.content_author || 'Anonyme')}</div><div><strong>Signalé par:</strong> ${escapeHtml(report.reported_by || 'Anonyme')}</div><div><strong>Date:</strong> ${new Date(report.created_at).toLocaleString('fr-FR')}</div></div></div><div style="flex: 0 0 auto;display:flex;flex-direction:column;gap:8px;"><button class="btn btn-secondary dismiss-report" data-report-id="${report.id}" style="padding:8px 16px;">✓ Ignorer</button><button class="btn btn-danger delete-reported-content" data-report-id="${report.id}" style="padding:8px 16px;">🗑️ Supprimer</button></div></div>`;
        reportsFeed.appendChild(card);
    });

    document.querySelectorAll('.dismiss-report').forEach(btn => {
        btn.addEventListener('click', async function () {
            await dismissReport(this.getAttribute('data-report-id'));
        });
    });

    document.querySelectorAll('.delete-reported-content').forEach(btn => {
        btn.addEventListener('click', async function () {
            await deleteReportedContent(this.getAttribute('data-report-id'));
        });
    });
}

async function dismissReport(reportId) {
    if (!await customConfirm('Confirmation', 'Êtes-vous sûr de vouloir ignorer ce signalement ?')) return;
    try {
        const formData = new FormData();
        formData.append('report_id', reportId);
        formData.append('action', 'dismiss');
        const response = await fetch(REPORT_API_URL, { method: 'POST', body: formData });
        const data = await parseJsonSafe(response);
        if (data && data.success) {
            showBackofficeMessage('Signalement ignoré avec succès', false, 3000);
            await loadReports();
        } else {
            showBackofficeMessage('Erreur lors de l\'ignorance du signalement', true);
        }
    } catch (err) {
        console.error('Error dismissing report:', err);
        showBackofficeMessage('Erreur lors de l\'ignorance du signalement', true);
    }
}

async function deleteReportedContent(reportId) {
    if (!await customConfirm('Attention', 'Êtes-vous sûr de vouloir supprimer ce contenu ? Cette action est irréversible.')) return;
    try {
        const formData = new FormData();
        formData.append('report_id', reportId);
        formData.append('action', 'delete_content');
        const response = await fetch(REPORT_API_URL, { method: 'POST', body: formData });
        const data = await parseJsonSafe(response);
        if (data && data.success) {
            showBackofficeMessage('Contenu supprimé avec succès', false, 3000);
            await loadReports();
            await fetchPosts();
        } else {
            showBackofficeMessage('Erreur lors de la suppression', true);
        }
    } catch (err) {
        console.error('Error deleting content:', err);
        showBackofficeMessage('Erreur lors de la suppression', true);
    }
}

async function loadReports() {
    const reports = await fetchReports();
    renderReports(reports);
}

function showReportsSection() {
    if (dashboardView) dashboardView.style.display = 'none';
    if (mainFeed) mainFeed.style.display = 'none';
    if (reportsSection) reportsSection.style.display = 'block';
    loadReports();
}

function hideReportsSection() {
    if (reportsSection) reportsSection.style.display = 'none';
    if (dashboardView) dashboardView.style.display = 'block';
    if (mainFeed) mainFeed.style.display = 'block';
}

if (reportsCard) {
    reportsCard.addEventListener('click', showReportsSection);
    reportsCard.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); showReportsSection(); }
    });
}
if (closeReportsBtn) closeReportsBtn.addEventListener('click', hideReportsSection);
loadReports();
// ============================================================================
// CUSTOM CONFIRMATION MODAL
// ============================================================================

function customConfirm(title, message) {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmModal');
        const modalTitle = document.getElementById('confirmModalTitle');
        const modalMessage = document.getElementById('confirmModalMessage');
        const okBtn = document.getElementById('confirmModalOk');
        const cancelBtn = document.getElementById('confirmModalCancel');

        if (!modal) {
            console.error('Confirm modal not found');
            resolve(confirm(message)); // Fallback to native
            return;
        }

        // Set content
        modalTitle.textContent = title;
        modalMessage.textContent = message;

        // Show modal
        modal.style.display = 'flex';

        // Handle clicks
        const handleOk = () => {
            modal.style.display = 'none';
            cleanup();
            resolve(true);
        };

        const handleCancel = () => {
            modal.style.display = 'none';
            cleanup();
            resolve(false);
        };

        const handleBackdrop = (e) => {
            if (e.target === modal) {
                handleCancel();
            }
        };

        const cleanup = () => {
            okBtn.removeEventListener('click', handleOk);
            cancelBtn.removeEventListener('click', handleCancel);
            modal.removeEventListener('click', handleBackdrop);
        };

        okBtn.addEventListener('click', handleOk);
        cancelBtn.addEventListener('click', handleCancel);
        modal.addEventListener('click', handleBackdrop);
    });
}
