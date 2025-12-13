// controller/builder-script.js - VERSION FINALE

const API_URL = '../../model/api.php';

let gameState = {
    target: 0,
    currentProd: 0,
    totalCost: 0,
    totalSpace: 0,
    items: []
};

let successShown = false;

// ================== INITIALISATION ==================
document.addEventListener('DOMContentLoaded', () => {
    initGame();
    setupDragDrop();

    document.getElementById('save-project-btn').addEventListener('click', saveProject);
    document.getElementById('reset-btn').addEventListener('click', resetGame);
});

async function initGame() {
    console.log('Initialisation du jeu...');
    
    const urlParams = new URLSearchParams(window.location.search);
    const consoParam = urlParams.get('conso');
    const userParam  = urlParams.get('user');
    
    if (consoParam && userParam) {
        gameState.target = Math.max(parseFloat(consoParam) * 12, 1000);
        document.getElementById('idUserBuilder').value = userParam;
        const ui = document.getElementById('user-info');
        if (ui) ui.innerHTML = `👤 ${userParam} | Objectif ≃ <strong>${gameState.target.toLocaleString()}</strong> kWh/an`;
    } else {
        gameState.target = 5000;
    }

    document.getElementById('target-val').textContent = gameState.target.toLocaleString();
    updateUI();
}

// ================== DRAG & DROP ==================
function setupDragDrop() {
    const draggables = document.querySelectorAll('.tool-item');
    const dropZone   = document.getElementById('drop-zone');

    draggables.forEach(draggable => {
        draggable.addEventListener('dragstart', e => {
            e.dataTransfer.setData('type',  draggable.dataset.type);
            e.dataTransfer.setData('prod',  draggable.dataset.prod);
            e.dataTransfer.setData('cost',  draggable.dataset.cost);
            e.dataTransfer.setData('space', draggable.dataset.space);
            e.dataTransfer.setData('icon',  draggable.querySelector('.icon').textContent);
        });
    });

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');

        const type  = e.dataTransfer.getData('type');
        const prod  = parseFloat(e.dataTransfer.getData('prod'));
        const cost  = parseFloat(e.dataTransfer.getData('cost'));
        const space = parseFloat(e.dataTransfer.getData('space'));
        const icon  = e.dataTransfer.getData('icon');

        if (type) addItem(type, prod, cost, space, icon);
    });
}

function addItem(type, prod, cost, space, icon) {
    gameState.currentProd += prod;
    gameState.totalCost   += cost;
    gameState.totalSpace  += space;
    gameState.items.push({ type, prod, cost, space });

    const elt = document.createElement('div');
    elt.className = 'built-item';
    elt.innerHTML = `<span class="icon">${icon}</span>`;
    elt.title = 'Cliquer pour supprimer';
    elt.addEventListener('click', () => removeItem(elt, type, prod, cost, space));

    const placeholder = document.querySelector('.placeholder-text');
    if (placeholder) placeholder.style.display = 'none';

    document.getElementById('drop-zone').appendChild(elt);
    updateUI();
}

function removeItem(elt, type, prod, cost, space) {
    gameState.currentProd -= prod;
    gameState.totalCost   -= cost;
    gameState.totalSpace  -= space;

    // suppression d'une occurrence dans le tableau
    const idx = gameState.items.findIndex(i => i.type === type && i.prod === prod && i.cost === cost && i.space === space);
    if (idx > -1) gameState.items.splice(idx, 1);

    elt.remove();

    if (gameState.items.length === 0) {
        const placeholder = document.querySelector('.placeholder-text');
        if (placeholder) placeholder.style.display = 'block';
    }

    updateUI();
}

// ================== MISE À JOUR UI ==================
function updateUI() {
    document.getElementById('current-prod').textContent = Math.round(gameState.currentProd).toLocaleString();
    document.getElementById('total-cost').textContent   = Math.round(gameState.totalCost).toLocaleString();
    document.getElementById('total-space').textContent  = gameState.totalSpace.toFixed(1);

    const pct = Math.min((gameState.currentProd / gameState.target) * 100, 100);
    const fill = document.getElementById('progress-fill');
    fill.style.width = pct + '%';

    const saveBtn = document.getElementById('save-project-btn');
    if (pct >= 100) {
        fill.style.background = '#2ecc71';
        saveBtn.disabled = false;
        if (!successShown) showSuccess();
    } else {
        fill.style.background = 'linear-gradient(90deg,#f1c40f,#e67e22)';
        saveBtn.disabled = true;
        successShown = false;
    }
}

// ================== MODAL SUCCÈS ==================
function showSuccess() {
    successShown = true;
    document.getElementById('modal-prod').textContent  = gameState.currentProd.toLocaleString();
    document.getElementById('modal-cost').textContent  = gameState.totalCost.toLocaleString();
    document.getElementById('modal-space').textContent = gameState.totalSpace.toFixed(1);
    document.getElementById('success-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('success-modal').style.display = 'none';
}

// ================== SAUVEGARDE DU PROJET ==================
async function saveProject() {
    const idUser = document.getElementById('idUserBuilder').value.trim();
    if (!idUser) {
        alert('⚠️ Entrez votre ID utilisateur avant de sauvegarder.');
        return;
    }

    const details = gameState.items.reduce((acc, item) => {
        acc[item.type] = (acc[item.type] || 0) + 1;
        return acc;
    }, {});

    const formData = new FormData();
    formData.append('action',     'saveProject');
    formData.append('idUser',     idUser);
    formData.append('objectif',   gameState.target);
    formData.append('production', gameState.currentProd);
    formData.append('cout',       gameState.totalCost);
    formData.append('espace',     gameState.totalSpace);
    formData.append('details',    JSON.stringify(details));

    try {
        const res  = await fetch(API_URL, { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            alert('🎉 Projet sauvegardé avec succès ! Retour au suivi des consommations.');
            
            // ⬇⬇⬇ MODIFICATION IMPORTANTE : REDIRECTION VERS FRONTOFFICE 1 ⬇⬇⬇
            window.location.href = '../frontoffice/index.html';
            // ⬆⬆⬆ FIN MODIFICATION ⬆⬆⬆

        } else {
            alert('❌ Erreur : ' + data.message);
        }
    } catch (err) {
        console.error(err);
        alert('❌ Erreur de connexion au serveur');
    }
}

// ================== RESET ==================
function resetGame() {
    if (confirm('Réinitialiser le projet ?')) {
        location.reload();
    }
}

console.log('builder-script.js chargé (version finale avec redirection).');
