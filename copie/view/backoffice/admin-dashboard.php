<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco-Track | Back </title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="admin-header">
        <h1>🎛️  Eco-Track</h1>
        <div class="header-right">
            <span class="admin-name">👤 Admin</span>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </div>
    
    <div class="admin-container">
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3 id="total-records">0</h3>
                    <p>Total Consommations</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚡</div>
                <div class="stat-info">
                    <h3 id="total-electricity">0</h3>
                    <p>Électricité (kWh)</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💧</div>
                <div class="stat-info">
                    <h3 id="total-water">0</h3>
                    <p>Eau (m³)</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔥</div>
                <div class="stat-info">
                    <h3 id="total-gas">0</h3>
                    <p>Gaz (m³)</p>
                </div>
            </div>
        </div>
        
        <div class="table-container">
            <div class="table-header">
                <h2>Gestion des Consommations</h2>
                <button onclick="window.location.href='../frontoffice/index.html'" class="btn-primary">+ Ajouter Nouvelle Consommation</button>
            </div>
            
            <div class="table-responsive">
                <table id="admin-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="check-all"></th>
                            <th>ID</th>
                            <th>ID Utilisateur</th>
                            <th>Type d'Énergie</th>
                            <th>Quantité</th>
                            <th>Date Début</th>
                            <th>Date Fin</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 30px;">
                                Chargement des données...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="bulk-actions">
                <button onclick="deleteSelected()" class="btn-danger">🗑️ Supprimer la sélection</button>
            </div>
        </div>
    </div>
    
    <!-- Modal d'édition -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Modifier la Consommation</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="edit-form">
                <input type="hidden" id="edit-id">
                
                <div class="form-group">
                    <label>ID Utilisateur</label>
                    <input type="text" id="edit-idUser" required>
                </div>
                
                <div class="form-group">
                    <label>Type d'Énergie</label>
                    <select id="edit-typeEnergie" required>
                        <option value="electricite">Électricité</option>
                        <option value="eau">Eau</option>
                        <option value="gaz">Gaz</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Quantité</label>
                    <input type="number" id="edit-quantite" required step="0.01">
                </div>
                
                <div class="form-group">
                    <label>Date Début</label>
                    <input type="date" id="edit-dateDebut" required>
                </div>
                
                <div class="form-group">
                    <label>Date Fin</label>
                    <input type="date" id="edit-dateFin" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeEditModal()" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-primary">💾 Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- IMPORTANT: Charger le script -->
    <script src="../../controller/admin-script.js"></script>
</body>
</html>
