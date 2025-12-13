<?php require_once '../../controller/functions.php'; ?>
<?php renderHeader("Builder Project"); ?>

<style>
/* Styles spécifiques au builder */
.builder-container {
    display: grid;
    grid-template-columns: 300px 1fr 350px;
    gap: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.tool-item {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    cursor: move;
    transition: all 0.3s ease;
}

.tool-item:hover {
    border-color: #22c55e;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
    transform: translateY(-2px);
}

.tool-item .icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

#drop-zone {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 3px dashed #cbd5e1;
    border-radius: 16px;
    min-height: 500px;
    display: flex;
    flex-wrap: wrap;
    align-content: flex-start;
    gap: 1rem;
    padding: 2rem;
    transition: all 0.3s ease;
}

#drop-zone.drag-over {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-color: #22c55e;
    transform: scale(1.02);
}

.built-item {
    width: 80px;
    height: 80px;
    background: white;
    border: 2px solid #22c55e;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.built-item:hover {
    background: #fee;
    border-color: #ef4444;
    transform: scale(1.1) rotate(-5deg);
}

.placeholder-text {
    width: 100%;
    text-align: center;
    color: #94a3b8;
    font-size: 1.2rem;
    padding: 3rem;
}

.progress-container {
    background: #e2e8f0;
    border-radius: 999px;
    height: 30px;
    overflow: hidden;
    position: relative;
}

#progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #f1c40f, #e67e22);
    transition: width 0.5s ease, background 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 1rem;
    color: white;
    font-weight: bold;
}

.stat-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
}

.stat-card .label {
    color: #64748b;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.stat-card .value {
    color: #1e293b;
    font-size: 1.5rem;
    font-weight: bold;
}

#success-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    max-width: 500px;
    text-align: center;
    animation: modalSlide 0.3s ease;
}

@keyframes modalSlide {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 1024px) {
    .builder-container {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-slate-800 mb-2">🏗️ Builder de Centrale Énergétique</h1>
    <p class="text-slate-500">Construisez votre centrale en glissant-déposant les éléments</p>
</div>

<div class="builder-container">
    <!-- Colonne gauche: Outils -->
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i data-lucide="wrench" class="w-5 h-5 text-brand-600"></i>
                Éléments disponibles
            </h2>
            
            <div class="space-y-3 max-h-[600px] overflow-y-auto">
                <div class="tool-item" draggable="true" data-type="solaire" data-prod="2500" data-cost="15000" data-space="20">
                    <div class="icon">🔆</div>
                    <div class="font-semibold text-sm text-slate-800">Panneau Solaire</div>
                    <div class="text-xs text-slate-500">2500 kWh/an</div>
                    <div class="text-xs text-slate-500">15,000 € | 20 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="eolienne" data-prod="5000" data-cost="35000" data-space="50">
                    <div class="icon">💨</div>
                    <div class="font-semibold text-sm text-slate-800">Éolienne</div>
                    <div class="text-xs text-slate-500">5000 kWh/an</div>
                    <div class="text-xs text-slate-500">35,000 € | 50 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="hydro" data-prod="8000" data-cost="50000" data-space="100">
                    <div class="icon">💧</div>
                    <div class="font-semibold text-sm text-slate-800">Hydroélectrique</div>
                    <div class="text-xs text-slate-500">8000 kWh/an</div>
                    <div class="text-xs text-slate-500">50,000 € | 100 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="biomasse" data-prod="3000" data-cost="20000" data-space="30">
                    <div class="icon">🌿</div>
                    <div class="font-semibold text-sm text-slate-800">Biomasse</div>
                    <div class="text-xs text-slate-500">3000 kWh/an</div>
                    <div class="text-xs text-slate-500">20,000 € | 30 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="geothermie" data-prod="4000" data-cost="30000" data-space="40">
                    <div class="icon">🌋</div>
                    <div class="font-semibold text-sm text-slate-800">Géothermie</div>
                    <div class="text-xs text-slate-500">4000 kWh/an</div>
                    <div class="text-xs text-slate-500">30,000 € | 40 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="batterie" data-prod="1200" data-cost="8000" data-space="5">
                    <div class="icon">🔋</div>
                    <div class="font-semibold text-sm text-slate-800">Batterie Stockage</div>
                    <div class="text-xs text-slate-500">1200 kWh/an</div>
                    <div class="text-xs text-slate-500">8,000 € | 5 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="solaire-compact" data-prod="1500" data-cost="10000" data-space="12">
                    <div class="icon">☀️</div>
                    <div class="font-semibold text-sm text-slate-800">Panneau Compact</div>
                    <div class="text-xs text-slate-500">1500 kWh/an</div>
                    <div class="text-xs text-slate-500">10,000 € | 12 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="eolienne-petite" data-prod="2000" data-cost="18000" data-space="25">
                    <div class="icon">🌬️</div>
                    <div class="font-semibold text-sm text-slate-800">Petite Éolienne</div>
                    <div class="text-xs text-slate-500">2000 kWh/an</div>
                    <div class="text-xs text-slate-500">18,000 € | 25 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="chaudiere-bois" data-prod="1800" data-cost="12000" data-space="8">
                    <div class="icon">🔥</div>
                    <div class="font-semibold text-sm text-slate-800">Chaudière Bois</div>
                    <div class="text-xs text-slate-500">1800 kWh/an</div>
                    <div class="text-xs text-slate-500">12,000 € | 8 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="pompe-chaleur" data-prod="2200" data-cost="14000" data-space="10">
                    <div class="icon">❄️</div>
                    <div class="font-semibold text-sm text-slate-800">Pompe à Chaleur</div>
                    <div class="text-xs text-slate-500">2200 kWh/an</div>
                    <div class="text-xs text-slate-500">14,000 € | 10 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="chauffe-eau-solaire" data-prod="800" data-cost="5000" data-space="6">
                    <div class="icon">🌡️</div>
                    <div class="font-semibold text-sm text-slate-800">Chauffe-eau Solaire</div>
                    <div class="text-xs text-slate-500">800 kWh/an</div>
                    <div class="text-xs text-slate-500">5,000 € | 6 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="micro-hydro" data-prod="3500" data-cost="25000" data-space="45">
                    <div class="icon">🌊</div>
                    <div class="font-semibold text-sm text-slate-800">Micro-Hydraulique</div>
                    <div class="text-xs text-slate-500">3500 kWh/an</div>
                    <div class="text-xs text-slate-500">25,000 € | 45 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="eolien-domestique" data-prod="2800" data-cost="22000" data-space="30">
                    <div class="icon">🌀</div>
                    <div class="font-semibold text-sm text-slate-800">Éolien Domestique</div>
                    <div class="text-xs text-slate-500">2800 kWh/an</div>
                    <div class="text-xs text-slate-500">22,000 € | 30 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="panneau-thermique" data-prod="1000" data-cost="7000" data-space="8">
                    <div class="icon">🌞</div>
                    <div class="font-semibold text-sm text-slate-800">Panneau Thermique</div>
                    <div class="text-xs text-slate-500">1000 kWh/an</div>
                    <div class="text-xs text-slate-500">7,000 € | 8 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="composteur" data-prod="600" data-cost="3000" data-space="4">
                    <div class="icon">🍃</div>
                    <div class="font-semibold text-sm text-slate-800">Composteur Énergétique</div>
                    <div class="text-xs text-slate-500">600 kWh/an</div>
                    <div class="text-xs text-slate-500">3,000 € | 4 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="generateur-vent" data-prod="1900" data-cost="15000" data-space="20">
                    <div class="icon">💨</div>
                    <div class="font-semibold text-sm text-slate-800">Générateur Vent</div>
                    <div class="text-xs text-slate-500">1900 kWh/an</div>
                    <div class="text-xs text-slate-500">15,000 € | 20 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="capteur-solaire" data-prod="900" data-cost="6000" data-space="7">
                    <div class="icon">📡</div>
                    <div class="font-semibold text-sm text-slate-800">Capteur Solaire</div>
                    <div class="text-xs text-slate-500">900 kWh/an</div>
                    <div class="text-xs text-slate-500">6,000 € | 7 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="turbine-eau" data-prod="4200" data-cost="28000" data-space="35">
                    <div class="icon">⚙️</div>
                    <div class="font-semibold text-sm text-slate-800">Turbine Eau</div>
                    <div class="text-xs text-slate-500">4200 kWh/an</div>
                    <div class="text-xs text-slate-500">28,000 € | 35 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="systeme-hybride" data-prod="3200" data-cost="24000" data-space="28">
                    <div class="icon">🔗</div>
                    <div class="font-semibold text-sm text-slate-800">Système Hybride</div>
                    <div class="text-xs text-slate-500">3200 kWh/an</div>
                    <div class="text-xs text-slate-500">24,000 € | 28 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="onduleur" data-prod="500" data-cost="4000" data-space="2">
                    <div class="icon">⚡</div>
                    <div class="font-semibold text-sm text-slate-800">Onduleur</div>
                    <div class="text-xs text-slate-500">500 kWh/an</div>
                    <div class="text-xs text-slate-500">4,000 € | 2 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="regulateur-charge" data-prod="300" data-cost="2500" data-space="1">
                    <div class="icon">🔌</div>
                    <div class="font-semibold text-sm text-slate-800">Régulateur Charge</div>
                    <div class="text-xs text-slate-500">300 kWh/an</div>
                    <div class="text-xs text-slate-500">2,500 € | 1 m²</div>
                </div>

                <div class="tool-item" draggable="true" data-type="panneau-portable" data-prod="400" data-cost="3500" data-space="3">
                    <div class="icon">📱</div>
                    <div class="font-semibold text-sm text-slate-800">Panneau Portable</div>
                    <div class="text-xs text-slate-500">400 kWh/an</div>
                    <div class="text-xs text-slate-500">3,500 € | 3 m²</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Colonne centrale: Zone de construction -->
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i data-lucide="building" class="w-5 h-5 text-brand-600"></i>
                Zone de Construction
            </h2>
            
            <div id="drop-zone">
                <div class="placeholder-text">
                    <i data-lucide="mouse-pointer-click" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                    <p class="font-semibold">Glissez les éléments ici</p>
                    <p class="text-sm mt-1">Cliquez sur un élément placé pour le supprimer</p>
                </div>
            </div>
        </div>

        <!-- Barre de progression -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <div class="flex justify-between items-center mb-3">
                <span class="font-semibold text-slate-800">Objectif de production</span>
                <span class="font-bold text-brand-600"><span id="target-val">0</span> kWh/an</span>
            </div>
            <div class="progress-container">
                <div id="progress-fill" style="width: 0%;">0%</div>
            </div>
            <p class="text-xs text-slate-500 mt-2 text-center" id="progress-message">
                Atteignez 100% pour sauvegarder votre projet
            </p>
        </div>
    </div>

    <!-- Colonne droite: Statistiques -->
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i data-lucide="bar-chart" class="w-5 h-5 text-brand-600"></i>
                Statistiques
            </h2>

            <div class="space-y-3">
                <div class="stat-card">
                    <div class="label">⚡ Production</div>
                    <div class="value"><span id="current-prod">0</span> kWh</div>
                </div>

                <div class="stat-card">
                    <div class="label">💰 Coût Total</div>
                    <div class="value"><span id="total-cost">0</span> €</div>
                </div>

                <div class="stat-card">
                    <div class="label">📏 Surface Totale</div>
                    <div class="value"><span id="total-space">0</span> m²</div>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <input type="text" id="idUserBuilder" placeholder="ID Utilisateur" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 p-3">
                
                <button id="save-project-btn" disabled class="w-full bg-gradient-to-r from-brand-500 to-brand-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-brand-600 hover:to-brand-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="save" class="inline w-4 h-4 mr-2"></i>
                    Sauvegarder le Projet
                </button>

                <button id="reset-btn" class="w-full bg-slate-200 text-slate-700 font-semibold py-3 px-6 rounded-xl hover:bg-slate-300 transition-all">
                    <i data-lucide="refresh-cw" class="inline w-4 h-4 mr-2"></i>
                    Réinitialiser
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de succès -->
<div id="success-modal">
    <div class="modal-content">
        <div class="text-6xl mb-4">🎉</div>
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Félicitations !</h2>
        <p class="text-slate-600 mb-6">Vous avez atteint votre objectif de production !</p>
        
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
                <div class="text-sm text-slate-500">Production</div>
                <div class="font-bold text-lg text-brand-600"><span id="modal-prod">0</span> kWh</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">Coût</div>
                <div class="font-bold text-lg text-blue-600"><span id="modal-cost">0</span> €</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">Surface</div>
                <div class="font-bold text-lg text-purple-600"><span id="modal-space">0</span> m²</div>
            </div>
        </div>

        <button onclick="closeModal()" class="bg-brand-600 text-white font-semibold py-3 px-8 rounded-xl hover:bg-brand-700 transition-all">
            Continuer
        </button>
    </div>
</div>

<!-- Modal de résultat du projet -->
<div id="project-result-modal" style="display: none;">
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-2xl w-full mx-4 transform transition-all animate-fade-in">
            <div class="text-center mb-6">
                <div class="text-6xl mb-4" id="result-emoji">📊</div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Résultat de votre Projet</h2>
                <div class="inline-flex px-6 py-3 rounded-full text-xl font-bold mb-4" id="result-badge">
                    <span id="result-status">En cours...</span>
                </div>
            </div>
            
            <div class="bg-slate-50 rounded-xl p-6 mb-6">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <div class="text-sm text-slate-500 mb-1">Production</div>
                        <div class="font-bold text-xl text-brand-600"><span id="result-prod">0</span> kWh</div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-slate-500 mb-1">Objectif</div>
                        <div class="font-bold text-xl text-slate-700"><span id="result-target">0</span> kWh</div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-slate-500 mb-1">Coût Total</div>
                        <div class="font-bold text-xl text-blue-600"><span id="result-cost">0</span> €</div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-slate-500 mb-1">Surface</div>
                        <div class="font-bold text-xl text-purple-600"><span id="result-space">0</span> m²</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <div class="text-center">
                        <div class="text-sm text-slate-500 mb-2">Score de Validation</div>
                        <div class="text-4xl font-bold" id="result-percentage">0%</div>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
                <h3 class="font-semibold text-blue-800 mb-2">💡 Conseils et Analyse</h3>
                <div id="result-advice" class="text-sm text-blue-700 space-y-2">
                    <p>Analyse en cours...</p>
                </div>
            </div>

            <button onclick="closeProjectResultModal()" class="w-full bg-gradient-to-r from-brand-500 to-brand-600 text-white font-semibold py-3 px-8 rounded-xl hover:from-brand-600 hover:to-brand-700 transition-all shadow-lg shadow-brand-500/30">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
// État du jeu
let gameState = {
    target: 0,
    currentProd: 0,
    totalCost: 0,
    totalSpace: 0,
    items: []
};

let successShown = false;

// Générer un target aléatoire entre 3000 et 12000 kWh/an
function generateRandomTarget() {
    const targets = [3000, 3500, 4000, 4500, 5000, 5500, 6000, 6500, 7000, 7500, 8000, 8500, 9000, 10000, 11000, 12000];
    return targets[Math.floor(Math.random() * targets.length)];
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    // Générer un target aléatoire
    gameState.target = generateRandomTarget();
    document.getElementById('target-val').textContent = gameState.target.toLocaleString();
    
    setupDragDrop();
    updateUI();
    
    document.getElementById('save-project-btn').addEventListener('click', saveProject);
    document.getElementById('reset-btn').addEventListener('click', resetGame);
});

// Drag & Drop
function setupDragDrop() {
    const draggables = document.querySelectorAll('.tool-item');
    const dropZone = document.getElementById('drop-zone');

    draggables.forEach(draggable => {
        draggable.addEventListener('dragstart', e => {
            e.dataTransfer.setData('type', draggable.dataset.type);
            e.dataTransfer.setData('prod', draggable.dataset.prod);
            e.dataTransfer.setData('cost', draggable.dataset.cost);
            e.dataTransfer.setData('space', draggable.dataset.space);
            e.dataTransfer.setData('icon', draggable.querySelector('.icon').textContent);
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
        
        const type = e.dataTransfer.getData('type');
        const prod = parseFloat(e.dataTransfer.getData('prod'));
        const cost = parseFloat(e.dataTransfer.getData('cost'));
        const space = parseFloat(e.dataTransfer.getData('space'));
        const icon = e.dataTransfer.getData('icon');
        
        if (type) addItem(type, prod, cost, space, icon);
    });
}

function addItem(type, prod, cost, space, icon) {
    gameState.currentProd += prod;
    gameState.totalCost += cost;
    gameState.totalSpace += space;
    gameState.items.push({ type, prod, cost, space });

    const elt = document.createElement('div');
    elt.className = 'built-item';
    elt.innerHTML = icon;
    elt.title = 'Cliquer pour supprimer';
    elt.addEventListener('click', () => removeItem(elt, type, prod, cost, space));

    const placeholder = document.querySelector('.placeholder-text');
    if (placeholder) placeholder.style.display = 'none';

    document.getElementById('drop-zone').appendChild(elt);
    updateUI();
}

function removeItem(elt, type, prod, cost, space) {
    gameState.currentProd -= prod;
    gameState.totalCost -= cost;
    gameState.totalSpace -= space;

    const idx = gameState.items.findIndex(i => 
        i.type === type && i.prod === prod && i.cost === cost && i.space === space
    );
    if (idx > -1) gameState.items.splice(idx, 1);

    elt.remove();

    if (gameState.items.length === 0) {
        const placeholder = document.querySelector('.placeholder-text');
        if (placeholder) placeholder.style.display = 'block';
    }

    updateUI();
}

function updateUI() {
    document.getElementById('current-prod').textContent = Math.round(gameState.currentProd).toLocaleString();
    document.getElementById('total-cost').textContent = Math.round(gameState.totalCost).toLocaleString();
    document.getElementById('total-space').textContent = gameState.totalSpace.toFixed(1);

    const pct = Math.min((gameState.currentProd / gameState.target) * 100, 100);
    const fill = document.getElementById('progress-fill');
    fill.style.width = pct + '%';
    fill.textContent = Math.round(pct) + '%';

    const saveBtn = document.getElementById('save-project-btn');
    
    if (pct >= 100) {
        fill.style.background = '#22c55e';
        saveBtn.disabled = false;
        document.getElementById('progress-message').textContent = '✅ Objectif atteint ! Vous pouvez sauvegarder.';
        if (!successShown) showSuccess();
    } else {
        fill.style.background = 'linear-gradient(90deg, #f1c40f, #e67e22)';
        saveBtn.disabled = true;
        document.getElementById('progress-message').textContent = `${Math.round(100 - pct)}% restant pour atteindre l'objectif`;
        successShown = false;
    }
    
    lucide.createIcons();
}

function showSuccess() {
    successShown = true;
    document.getElementById('modal-prod').textContent = gameState.currentProd.toLocaleString();
    document.getElementById('modal-cost').textContent = gameState.totalCost.toLocaleString();
    document.getElementById('modal-space').textContent = gameState.totalSpace.toFixed(1);
    document.getElementById('success-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('success-modal').style.display = 'none';
}

function calculateProjectScore() {
    // Score basé sur plusieurs critères
    let score = 0;
    let maxScore = 100;
    
    // 1. Respect de l'objectif de production (40 points)
    const productionRatio = Math.min(gameState.currentProd / gameState.target, 1);
    score += productionRatio * 40;
    
    // 2. Efficacité coût/production (30 points)
    // Meilleur score si coût par kWh est raisonnable (entre 2 et 5 €/kWh)
    const costPerKwh = gameState.currentProd > 0 ? gameState.totalCost / gameState.currentProd : 0;
    let costScore = 0;
    if (costPerKwh >= 2 && costPerKwh <= 5) {
        costScore = 30;
    } else if (costPerKwh < 2) {
        costScore = 20; // Trop bon marché peut être suspect
    } else if (costPerKwh <= 8) {
        costScore = 20 - ((costPerKwh - 5) / 3) * 10;
    } else {
        costScore = Math.max(0, 10 - (costPerKwh - 8) * 2);
    }
    score += costScore;
    
    // 3. Efficacité surface/production (20 points)
    // Meilleur score si production par m² est élevée (> 50 kWh/m²)
    const prodPerM2 = gameState.totalSpace > 0 ? gameState.currentProd / gameState.totalSpace : 0;
    let spaceScore = 0;
    if (prodPerM2 >= 50) {
        spaceScore = 20;
    } else if (prodPerM2 >= 30) {
        spaceScore = 15;
    } else if (prodPerM2 >= 20) {
        spaceScore = 10;
    } else {
        spaceScore = Math.max(0, prodPerM2 / 20 * 10);
    }
    score += spaceScore;
    
    // 4. Diversité des sources (10 points)
    const uniqueTypes = new Set(gameState.items.map(item => item.type)).size;
    const diversityScore = Math.min(uniqueTypes / 5 * 10, 10);
    score += diversityScore;
    
    return Math.round(Math.min(score, maxScore));
}

function generateAdvice(score, productionRatio, costPerKwh, prodPerM2) {
    let advice = [];
    
    if (productionRatio >= 1) {
        advice.push('✅ Excellent ! Vous avez atteint votre objectif de production.');
    } else {
        const missing = gameState.target - gameState.currentProd;
        advice.push(`⚠️ Il vous manque ${Math.round(missing).toLocaleString()} kWh pour atteindre l'objectif.`);
        advice.push('💡 Ajoutez plus d\'éléments de production pour compléter votre projet.');
    }
    
    if (costPerKwh > 8) {
        advice.push('💰 Votre coût par kWh est élevé. Pensez à utiliser des sources plus économiques.');
    } else if (costPerKwh >= 2 && costPerKwh <= 5) {
        advice.push('✅ Votre rapport coût/production est optimal.');
    } else {
        advice.push('💡 Votre projet est très économique, mais vérifiez la qualité des équipements.');
    }
    
    if (prodPerM2 < 20) {
        advice.push('📏 Votre surface utilisée est importante par rapport à la production.');
        advice.push('💡 Considérez des équipements plus compacts ou plus efficaces.');
    } else if (prodPerM2 >= 50) {
        advice.push('✅ Excellente utilisation de l\'espace disponible !');
    }
    
    if (gameState.items.length < 3) {
        advice.push('🔗 Diversifiez vos sources d\'énergie pour plus de résilience.');
    }
    
    if (score >= 80) {
        advice.push('🎉 Félicitations ! Votre projet est très bien conçu.');
    } else if (score >= 60) {
        advice.push('👍 Votre projet est correct, mais peut être amélioré.');
    } else {
        advice.push('📚 Continuez à optimiser votre projet pour de meilleurs résultats.');
    }
    
    return advice;
}

function saveProject() {
    const idUser = document.getElementById('idUserBuilder').value.trim();
    
    if (!idUser) {
        alert('⚠️ Veuillez entrer votre ID utilisateur avant de sauvegarder.');
        return;
    }

    const details = gameState.items.reduce((acc, item) => {
        acc[item.type] = (acc[item.type] || 0) + 1;
        return acc;
    }, {});

    // Calculer le score et les métriques
    const score = calculateProjectScore();
    const productionRatio = Math.min(gameState.currentProd / gameState.target, 1);
    const costPerKwh = gameState.currentProd > 0 ? gameState.totalCost / gameState.currentProd : 0;
    const prodPerM2 = gameState.totalSpace > 0 ? gameState.currentProd / gameState.totalSpace : 0;
    const advice = generateAdvice(score, productionRatio, costPerKwh, prodPerM2);
    
    // Déterminer le statut et l'emoji
    let status, emoji, badgeClass;
    if (score >= 80) {
        status = 'Excellent';
        emoji = '🎉';
        badgeClass = 'bg-green-100 text-green-800 border-green-200';
    } else if (score >= 60) {
        status = 'Bon';
        emoji = '👍';
        badgeClass = 'bg-blue-100 text-blue-800 border-blue-200';
    } else if (score >= 40) {
        status = 'Moyen';
        emoji = '⚠️';
        badgeClass = 'bg-yellow-100 text-yellow-800 border-yellow-200';
    } else {
        status = 'À améliorer';
        emoji = '📊';
        badgeClass = 'bg-orange-100 text-orange-800 border-orange-200';
    }
    
    // Afficher le modal de résultat
    document.getElementById('result-emoji').textContent = emoji;
    document.getElementById('result-status').textContent = status;
    document.getElementById('result-badge').className = 'inline-flex px-6 py-3 rounded-full text-xl font-bold mb-4 border-2 ' + badgeClass;
    document.getElementById('result-prod').textContent = Math.round(gameState.currentProd).toLocaleString();
    document.getElementById('result-target').textContent = gameState.target.toLocaleString();
    document.getElementById('result-cost').textContent = Math.round(gameState.totalCost).toLocaleString();
    document.getElementById('result-space').textContent = gameState.totalSpace.toFixed(1);
    document.getElementById('result-percentage').textContent = score + '%';
    document.getElementById('result-percentage').className = 'text-4xl font-bold ' + 
        (score >= 80 ? 'text-green-600' : score >= 60 ? 'text-blue-600' : score >= 40 ? 'text-yellow-600' : 'text-orange-600');
    
    const adviceHtml = advice.map(a => `<p>${a}</p>`).join('');
    document.getElementById('result-advice').innerHTML = adviceHtml;
    
    document.getElementById('project-result-modal').style.display = 'flex';
    
    // Simulation de sauvegarde (vous pouvez ajouter un appel PHP ici)
    console.log('Projet sauvegardé:', {
        idUser,
        objectif: gameState.target,
        production: gameState.currentProd,
        cout: gameState.totalCost,
        espace: gameState.totalSpace,
        score: score,
        details
    });
}

function closeProjectResultModal() {
    document.getElementById('project-result-modal').style.display = 'none';
}

function resetGame() {
    if (confirm('Voulez-vous vraiment réinitialiser le projet ?')) {
        // Générer un nouveau target aléatoire
        gameState.target = generateRandomTarget();
        gameState.currentProd = 0;
        gameState.totalCost = 0;
        gameState.totalSpace = 0;
        gameState.items = [];
        successShown = false;
        
        // Vider la zone de construction
        document.getElementById('drop-zone').innerHTML = `
            <div class="placeholder-text">
                <i data-lucide="mouse-pointer-click" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                <p class="font-semibold">Glissez les éléments ici</p>
                <p class="text-sm mt-1">Cliquez sur un élément placé pour le supprimer</p>
            </div>
        `;
        
        document.getElementById('target-val').textContent = gameState.target.toLocaleString();
        updateUI();
        lucide.createIcons();
    }
}
</script>

<?php renderFooter(); ?>
