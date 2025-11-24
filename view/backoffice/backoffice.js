// Backoffice script: final cleaned implementation

let comments = [];

// Dashboard counters
let sentCount = 0;
let commentCount = 0;
let editCount = 0;
let deleteCount = 0;

function updateDashboard() {
    const sentEl = document.getElementById('sentCount');
    const commentEl = document.getElementById('commentCount');
    const editEl = document.getElementById('editCount');
    const delEl = document.getElementById('deleteCount');
    if (sentEl) sentEl.textContent = sentCount;
    if (commentEl) commentEl.textContent = commentCount;
    if (editEl) editEl.textContent = editCount;
    if (delEl) delEl.textContent = deleteCount;
}

// Non-blocking user message helper (writes to #backofficeStatus if present)
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
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]);
    });
}

function renderPosts() {
    const backofficeFeed = document.getElementById('backofficeFeed');
    if (!backofficeFeed) return;
    // clear and add a strong heading + toolbar + debug area
    backofficeFeed.innerHTML = '<h2 style="margin-top:0;">Gestion des publications et commentaires</h2>' +
        '<div class="feed-toolbar" style="margin:12px 0; display:flex; gap:8px; align-items:center;"><button id="refreshFeed" class="btn-primary">Rafraîchir</button></div>' +
        '<div id="backofficeStatus" style="margin-bottom:8px;color:#2b3b36;font-weight:600"></div>';

    if (!Array.isArray(comments) || comments.length === 0) {
        backofficeFeed.innerHTML += '<div style="color:#666">Aucune publication pour le moment.</div>';
        sentCount = 0;
        commentCount = 0;
        updateDashboard();
        return;
    }

    // Render each comment as a clear card so it's visible
    comments.forEach(comment => {
        const commentEl = document.createElement('div');
        commentEl.style.background = '#ffffff';
        commentEl.style.border = '1px solid #e6e6e6';
        commentEl.style.padding = '14px';
        commentEl.style.marginBottom = '12px';
        commentEl.style.borderRadius = '8px';
        commentEl.classList.add('publication');

        const author = escapeHtml(comment.send_by || 'Anonyme');
        const body = escapeHtml(comment.contenu || '');
        const time = escapeHtml(comment.time || '');

        commentEl.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <div style="flex:1">
                    <div style="font-weight:700;color:#2b3b36;margin-bottom:6px;">${author}</div>
                    <div class="comment-text" style="color:#222;">${body}</div>
                    <div class="pub-date" style="color:#666;margin-top:8px;font-size:0.9em;">${time}</div>
                </div>
                <div style="flex:0 0 auto;display:flex;flex-direction:column;gap:8px;margin-left:12px;">
                    <button class="view-btn" data-id="${comment.id}" style="background:#357a38;color:#fff;border:none;padding:8px 10px;border-radius:6px;">Voir</button>
                    <button class="edit-btn" data-id="${comment.id}" style="background:#2b6f2e;color:#fff;border:none;padding:8px 10px;border-radius:6px;">Modifier</button>
                    <button class="delete-btn" data-id="${comment.id}" style="background:#D9534F;color:#fff;border:none;padding:8px 10px;border-radius:6px;">Supprimer</button>
                </div>
            </div>
        `;
        backofficeFeed.appendChild(commentEl);
    });

    sentCount = comments.length;
    commentCount = comments.length;
    updateDashboard();

    // update status
    const status = document.getElementById('backofficeStatus');
    if (status) {
        status.textContent = `Commentaires chargés: ${comments.length}`;
    }
}

// Modal creation for viewing/editing a single comment
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
        const id = this.getAttribute('data-id');
        const contenu = document.getElementById('modalContent').value;
        const send_by = document.getElementById('modalAuthor').value || '';
        fetch('../../controller/commentcontroller.php', {
            method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&contenu=${encodeURIComponent(contenu)}&send_by=${encodeURIComponent(send_by)}`
        }).then(parseJsonSafe).then(data => {
            if (data && data.success) { closeModal(); fetchComments(); } else { showBackofficeMessage('Erreur: ' + (data && data.error ? data.error : 'Réponse invalide'), true); }
        }).catch(err => { showBackofficeMessage('Erreur lors de la sauvegarde. Voir console. ' + (err.raw || err.message || ''), true); console.error(err); });
    });
    document.getElementById('modalDelete').addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const btn = this;
        btn.disabled = true;
        const prev = btn.textContent;
        btn.textContent = 'Suppression...';
        fetch('../../controller/commentcontroller.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `id=${id}` })
            .then(parseJsonSafe).then(data => { if (data && data.success) { closeModal(); fetchComments(); } else { showBackofficeMessage('Erreur suppression: ' + (data && data.error ? data.error : 'Réponse invalide'), true); } })
            .catch(err => { showBackofficeMessage('Erreur lors de la suppression. Voir console. ' + (err.raw || err.message || ''), true); console.error(err); })
            .finally(() => { btn.disabled = false; btn.textContent = prev; });
    });
}

function showModalForComment(id) {
    ensureModal();
    const modal = document.getElementById('commentModal');
    const modalAuthor = document.getElementById('modalAuthor');
    const modalTime = document.getElementById('modalTime');
    const modalContent = document.getElementById('modalContent');
    const modalSave = document.getElementById('modalSave');
    const modalDelete = document.getElementById('modalDelete');
    const c = comments.find(x => String(x.id) === String(id));
    if (!c) { showBackofficeMessage('Commentaire introuvable', true); return; }
    modalAuthor.textContent = c.send_by || 'Anonyme';
    modalTime.textContent = c.time || '';
    modalContent.value = c.contenu || '';
    modalSave.setAttribute('data-id', c.id);
    modalDelete.setAttribute('data-id', c.id);
    modal.style.display = 'flex';
}

function closeModal() { const modal = document.getElementById('commentModal'); if (modal) modal.style.display = 'none'; }

function fetchComments() {
    // Build an absolute URL relative to the current document to avoid incorrect relative paths
    const url = new URL('../../controller/commentcontroller.php', window.location.href).href;
    console.log('Fetching comments from', url);
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
                comments = data || [];
                renderPosts();
            } catch (ex) {
                console.error('Failed to parse JSON from controller response', ex);
                const backofficeFeed = document.getElementById('backofficeFeed');
                if (backofficeFeed) backofficeFeed.innerHTML = '<div class="server-error">Réponse invalide du serveur — voir debug ci-dessous.<pre style="white-space:pre-wrap; color:#a33;">' + text + '</pre></div>';
            }
        })
        .catch(err => {
            console.error('Network or fetch error when requesting comments:', err);
            const backofficeFeed = document.getElementById('backofficeFeed');
            if (backofficeFeed) backofficeFeed.innerHTML = '<div class="server-error">Erreur réseau lors du chargement des publications. Voir console pour détails.</div>';
        });
}

document.addEventListener('DOMContentLoaded', function () {
    // initial load
    fetchComments();

    // event delegation
    document.addEventListener('click', function (e) {
        if (!e.target) return;

        // Edit
        if (e.target.classList && e.target.classList.contains('edit-btn')) {
            const id = e.target.getAttribute('data-id');
            // open modal for editing (author + content) — same interaction as frontoffice
            showModalForComment(id);
        }

        // View
        if (e.target.classList && e.target.classList.contains('view-btn')) {
            const id = e.target.getAttribute('data-id');
            showModalForComment(id);
        }

        // Toolbar: refresh
        if (e.target.id === 'refreshFeed') {
            fetchComments();
        }

        

        // Delete (no confirm) — perform deletion immediately
        if (e.target.classList && e.target.classList.contains('delete-btn')) {
            const id = e.target.getAttribute('data-id');
            const btn = e.target;
            btn.disabled = true;
            const prev = btn.textContent;
            btn.textContent = 'Suppression...';
            fetch('../../controller/commentcontroller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            })
            .then(parseJsonSafe)
            .then(data => {
                if (data && data.success) {
                    deleteCount++;
                    fetchComments();
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
    });
});
