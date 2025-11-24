const createPostBtn = document.getElementById('createPostBtn');
const modal = document.getElementById('postModal');
const closeModal = document.getElementById('closeModal');
const submitPost = document.getElementById('submitPost');
const postContent = document.getElementById('postContent');
const postAuthor = document.getElementById('postAuthor');
const ecoFeed = document.getElementById('ecoFeed');
const postsContainer = document.getElementById('postsContainer');
const postMessage = document.getElementById('postMessage');
let editingId = null; // null => creating new post; otherwise updating existing post id
const API_URL = new URL('../../controller/commentcontroller.php', window.location.href).href;
console.log('frontoffice.js loaded — API:', API_URL);

// Helper: parse JSON only if server returned JSON
function parseJsonSafe(response) {
    const ct = response.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
        return response.text().then(text => { throw new Error('Invalid JSON response:\n' + text); });
    }
    return response.json();
}

// Ouvre la modale de création de post
createPostBtn.addEventListener('click', () => {
    // show modal when user clicks the button (centered with animation)
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        // small timeout to allow CSS transition and then focus content
        setTimeout(() => {
            postContent.focus();
            // auto-resize the textarea to fit current content
            autoResizeTextarea(postContent);
        }, 120);
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
    postContent.value = '';
    postAuthor.value = '';
    postMessage.style.display = 'none';
    editingId = null;
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
        postContent.value = '';
        postAuthor.value = '';
        editingId = null;
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
        const sendBy = item.send_by || 'Anonyme';
        const contenu = item.contenu || '';
        const time = item.time || '';
        // Build a post card with management buttons and a comment box
        article.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                <div style="flex:1">
                    <p><strong>${sendBy}</strong>: <span class="post-body">${contenu}</span></p>
                    <div class="pub-date">${time}</div>
                </div>
                <div style="flex:0 0 auto;">
                    <div class="actions" style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
                        <button class="view-post btn btn-secondary" data-id="${item.id}" aria-label="Voir la publication">Voir</button>
                        <button class="edit-post btn btn-primary" data-id="${item.id}" aria-label="Modifier la publication">Modifier</button>
                        <button class="delete-post btn btn-danger" data-id="${item.id}" aria-label="Supprimer la publication">Supprimer</button>
                    </div>
                </div>
            </div>
            <div class="comments-zone" style="margin-top:10px;padding-top:10px;border-top:1px solid #eee;">
                <div class="comments-list" style="margin-bottom:8px;"></div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input class="comment-author" placeholder="Votre nom (optionnel)" style="width:160px;padding:8px;border:1px solid #ddd;border-radius:6px;" />
                    <input class="comment-input" placeholder="Écrire un commentaire..." style="flex:1;padding:8px;border:1px solid #ddd;border-radius:6px;" />
                    <button class="comment-btn btn btn-primary" data-parent-id="${item.id}">Commenter</button>
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
            renderComments(data);
        })
        .catch(err => {
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
});

// Ajout d'une publication avec zone de commentaires
submitPost.addEventListener('click', () => {
    const content = postContent.value.trim();
    const sendBy = (postAuthor.value || 'Anonyme').trim();
    if (content.length === 0) {
        alert('Veuillez écrire quelque chose pour publier.');
        return;
    }
    submitPost.disabled = true;
    // If editingId is set, update existing post; otherwise create new
    if (editingId) {
        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${editingId}&contenu=${encodeURIComponent(content)}&send_by=${encodeURIComponent(sendBy)}`
        }).then(parseJsonSafe).then(data => {
            if (data && data.success) {
                // reset and refresh
                editingId = null;
                if (modal) {
                    modal.classList.remove('open');
                    modal.setAttribute('aria-hidden', 'true');
                    setTimeout(() => { modal.style.display = 'none'; }, 260);
                }
                postContent.value = ''; postAuthor.value = '';
                fetchComments();
            } else {
                alert('Erreur lors de la mise à jour: ' + (data && data.error ? data.error : ''));
            }
        }).catch(err => { console.error('Update error', err); alert('Erreur lors de la mise à jour.'); })
          .finally(() => { submitPost.disabled = false; });
    } else {
        // Envoi du post au backend (create)
        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `send_by=${encodeURIComponent(sendBy)}&contenu=${encodeURIComponent(content)}`
        })
        .then(parseJsonSafe)
        .then(data => {
            if (data && data.success) {
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
            const now = new Date().toLocaleString();
            appendLocalPost(sendBy, content, now, '(publié localement, serveur indisponible)');
        })
        .finally(() => { submitPost.disabled = false; });
    }
});

// Handle comment, edit, delete and view actions via event delegation
postsContainer.addEventListener('click', function (e) {
    // Comment on a post (creates a new comment record)
    if (e.target.classList.contains('comment-btn')) {
        const container = e.target.closest('.comments-zone');
        const input = container.querySelector('.comment-input');
        const authorInput = container.querySelector('.comment-author'); // (no change, just for context)
        const text = input.value.trim();
        const author = (authorInput && authorInput.value.trim()) || 'Anonyme';
        if (text.length === 0) { alert('Écris un commentaire avant de répondre.'); return; }
        const parentId = e.target.getAttribute('data-parent-id');
        // Store parent id inside the content for now (for reply threading)
        const payload = `send_by=${encodeURIComponent(author)}&contenu=${encodeURIComponent('(reply to ' + parentId + ') ' + text)}`;
        fetch(API_URL, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: payload })
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
    if (e.target.classList && e.target.classList.contains('edit-post')) {
        const id = e.target.getAttribute('data-id');
        // fetch the single post data then open modal for editing
        fetch(API_URL).then(parseJsonSafe).then(data => {
            const post = (data || []).find(p => String(p.id) === String(id));
            if (!post) { alert('Publication introuvable.'); return; }
            document.getElementById('postAuthor').value = post.send_by || '';
            document.getElementById('postContent').value = post.contenu || '';
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            // Enable fields for editing and show submit
            postAuthor.disabled = false;
            postContent.disabled = false;
            submitPost.disabled = false;
            submitPost.style.display = '';
            editingId = id; // will make the Save button send update
        }).catch(err => { console.error('Edit fetch error', err); alert('Impossible d\'ouvrir la publication pour modification.'); });
    }

    // Delete post (no confirm dialog — perform deletion immediately)
    if (e.target.classList && e.target.classList.contains('delete-post')) {
        const id = e.target.getAttribute('data-id');
        const btn = e.target;
        btn.disabled = true;
        const prevText = btn.textContent;
        btn.textContent = 'Suppression...';
        fetch(API_URL, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `id=${id}` })
            .then(parseJsonSafe)
            .then(data => {
                if (data && data.success) { fetchComments(); } else { alert('Erreur lors de la suppression: ' + (data && data.error ? data.error : '')); }
            })
            .catch(err => { console.error('Delete error', err); alert('Erreur lors de la suppression.'); })
            .finally(() => { btn.disabled = false; btn.textContent = prevText; });
    }

    // View post - open a basic modal by reusing the create modal for simplicity
    if (e.target.classList && e.target.classList.contains('view-post')) {
        const id = e.target.getAttribute('data-id');
        // Find the post data from the last fetched list by re-fetching and opening modal when found
        fetch(API_URL)
            .then(parseJsonSafe)
            .then(data => {
                const post = (data || []).find(p => String(p.id) === String(id));
                if (!post) { alert('Publication introuvable.'); return; }
                // populate modal fields for quick editing/viewing
                document.getElementById('postAuthor').value = post.send_by || '';
                document.getElementById('postContent').value = post.contenu || '';
                // Disable inputs so this modal is view-only
                postAuthor.disabled = true;
                postContent.disabled = true;
                // hide/disable the submit button while viewing
                submitPost.disabled = true;
                submitPost.style.display = 'none';
                modal.style.display = 'block';
                modal.setAttribute('aria-hidden', 'false');
                // Do NOT set editingId for view mode
            })
            .catch(err => { console.error('View fetch error', err); alert('Impossible d\'ouvrir la publication'); });
    }

    // Edit/delete comments (future: only allow if user is author or admin)
    if (e.target.classList.contains('edit-comment')) {
        const commentEl = e.target.closest('.comment-item');
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
        saveBtn.addEventListener('click', function() {
            const newContent = editInput.value.trim();
            if (!newContent) return alert('Le commentaire ne peut pas être vide.');
            // For now, just update locally (future: send to backend)
            // TODO: send update to backend with comment id
            contentEl.textContent = newContent;
            editInput.replaceWith(contentEl);
            saveBtn.remove();
            e.target.style.display = '';
        });
    }
    if (e.target.classList.contains('delete-comment')) {
        const commentEl = e.target.closest('.comment-item');
        if (!commentEl) return;
        // TODO: send delete to backend with comment id
        commentEl.remove();
    }
});

// Append a local-only post to the top of the list with an optional note
function appendLocalPost(author, content, time, note) {
    const article = document.createElement('article');
    article.className = 'publication';
    const sendBy = author || 'Anonyme';
    article.innerHTML = `
        <p><strong>${sendBy}</strong>: ${content}</p>
        <div class="pub-date">${time} <span style="color:#a33; font-weight:600;">${note}</span></div>
    `;
    // insert at top
    if (postsContainer.firstChild) postsContainer.insertBefore(article, postsContainer.firstChild);
    else postsContainer.appendChild(article);
}
