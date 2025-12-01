<?php
// smartinnovators/PDO/View/backoffice/Participation/Participation.php
// Version Participation avec design innovant EcoTrack

class Participation {
    private $idParticipation;
    private $idEvenement;
    private $contenu;
    private $dateParticipation;

    public function __construct($idParticipation, $idEvenement, $contenu, $dateParticipation) {
        $this->idParticipation = $idParticipation;
        $this->idEvenement = $idEvenement;
        $this->contenu = $contenu;
        $this->dateParticipation = $dateParticipation;
    }

    public function getIdParticipation() { return $this->idParticipation; }
    public function getIdEvenement() { return $this->idEvenement; }
    public function getContenu() { return $this->contenu; }
    public function getDateParticipation() { return $this->dateParticipation; }

    public function setIdParticipation($id) { $this->idParticipation = $id; }
    public function setIdEvenement($id) { $this->idEvenement = $id; }
    public function setContenu($contenu) { $this->contenu = $contenu; }
    public function setDateParticipation($date) { $this->dateParticipation = $date; }
}

class ParticipationManager {
    private $db;
    private $connected = false;

    public function __construct() {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=smartinnovators', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connected = true;
        } catch(PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            $this->connected = false;
        }
    }

    private function checkConnection() {
        if (!$this->connected || !$this->db) {
            throw new Exception("Database connection not available");
        }
        return true;
    }

    // CREATE - Ajouter une participation
    public function addParticipation($participation) {
        try {
            $this->checkConnection();
            $sql = "INSERT INTO participation (idEvenement, contenu, dateParticipation) 
                    VALUES (:idEvenement, :contenu, :dateParticipation)";
            $query = $this->db->prepare($sql);
            $query->execute([
                'idEvenement' => $participation->getIdEvenement(),
                'contenu' => $participation->getContenu(),
                'dateParticipation' => $participation->getDateParticipation()
            ]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erreur addParticipation: " . $e->getMessage());
            return false;
        }
    }

    // READ - Récupérer toutes les participations
    public function getAllParticipations() {
        try {
            $this->checkConnection();
            $sql = "SELECT * FROM participation ORDER BY dateParticipation DESC";
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getAllParticipations: " . $e->getMessage());
            return [];
        }
    }

    // READ - Récupérer une participation par ID
    public function getParticipationById($id) {
        try {
            $this->checkConnection();
            $sql = "SELECT * FROM participation WHERE idParticipation = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getParticipationById: " . $e->getMessage());
            return null;
        }
    }

    // UPDATE - Modifier une participation
    public function updateParticipation($participation) {
        try {
            $this->checkConnection();
            $sql = "UPDATE participation SET idEvenement = :idEvenement, contenu = :contenu, 
                    dateParticipation = :dateParticipation WHERE idParticipation = :id";
            $query = $this->db->prepare($sql);
            $query->execute([
                'id' => $participation->getIdParticipation(),
                'idEvenement' => $participation->getIdEvenement(),
                'contenu' => $participation->getContenu(),
                'dateParticipation' => $participation->getDateParticipation()
            ]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erreur updateParticipation: " . $e->getMessage());
            return false;
        }
    }

    // DELETE - Supprimer une participation
    public function deleteParticipation($idParticipation) {
        try {
            $this->checkConnection();
            $sql = "DELETE FROM participation WHERE idParticipation = :idParticipation";
            $query = $this->db->prepare($sql);
            $query->execute(['idParticipation' => $idParticipation]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erreur deleteParticipation: " . $e->getMessage());
            return false;
        }
    }

    public function countParticipations() {
        try {
            $this->checkConnection();
            $sql = "SELECT COUNT(*) as total FROM participation";
            $query = $this->db->prepare($sql);
            $query->execute();
            $result = $query->fetch();
            return $result['total'];
        } catch (Exception $e) {
            error_log("Erreur countParticipations: " . $e->getMessage());
            return 0;
        }
    }

    public function isConnected() {
        return $this->connected;
    }
}

// INITIALISATION
$participationManager = new ParticipationManager();
$participations = $participationManager->getAllParticipations();
$message = '';
$editingParticipation = null;

// Message d'erreur de connexion
if (!$participationManager->isConnected()) {
    $message = '<div class="message warning"><i class="fas fa-exclamation-triangle"></i>Connexion à la base de données échouée. Vérifiez la configuration.</div>';
}

// GESTION DES MESSAGES
if (isset($_GET['success'])) {
    $message = '<div class="message success"><i class="fas fa-check-circle"></i>' . htmlspecialchars($_GET['success']) . '</div>';
}
if (isset($_GET['error'])) {
    $message = '<div class="message error"><i class="fas fa-exclamation-circle"></i>' . htmlspecialchars($_GET['error']) . '</div>';
}

// TRAITEMENT AJOUT PARTICIPATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_participation') {
        if (!empty($_POST['idEvenement']) && !empty($_POST['contenu'])) {
            $participation = new Participation(
                null,
                $_POST['idEvenement'],
                $_POST['contenu'],
                date('Y-m-d H:i:s')
            );

            $result = $participationManager->addParticipation($participation);
            if ($result) {
                header('Location: Participation.php?success=Participation ajoutée avec succès');
                exit;
            } else {
                $message = '<div class="message error"><i class="fas fa-exclamation-circle"></i>Erreur lors de l\'ajout de la participation</div>';
            }
        } else {
            $message = '<div class="message warning"><i class="fas fa-exclamation-triangle"></i>Veuillez remplir tous les champs obligatoires</div>';
        }
    }
    // TRAITEMENT MODIFICATION PARTICIPATION
    elseif ($_POST['action'] === 'edit_participation') {
        if (!empty($_POST['idParticipation']) && !empty($_POST['idEvenement']) && !empty($_POST['contenu'])) {
            $participation = new Participation(
                $_POST['idParticipation'],
                $_POST['idEvenement'],
                $_POST['contenu'],
                $_POST['dateParticipation']
            );

            if ($participationManager->updateParticipation($participation)) {
                header('Location: Participation.php?success=Participation modifiée avec succès');
                exit;
            } else {
                $message = '<div class="message error"><i class="fas fa-exclamation-circle"></i>Erreur lors de la modification de la participation</div>';
            }
        }
    }
}

// TRAITEMENT SUPPRESSION PARTICIPATION
if (isset($_GET['delete_id'])) {
    $idParticipation = intval($_GET['delete_id']);
    if ($idParticipation > 0) {
        if ($participationManager->deleteParticipation($idParticipation)) {
            header('Location: Participation.php?success=Participation supprimée avec succès');
            exit;
        } else {
            $message = '<div class="message error"><i class="fas fa-exclamation-circle"></i>Erreur lors de la suppression de la participation</div>';
        }
    }
}

// MODE ÉDITION
if (isset($_GET['edit_id'])) {
    $editingParticipation = $participationManager->getParticipationById($_GET['edit_id']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Participations - Backoffice EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --tech-cyan: #00f5d4;
            --tech-blue: #00bbf9;
            --tech-purple: #9b5de5;
            --tech-green: #00cc88;
            --tech-pink: #f15bb5;
            --tech-yellow: #fee440;
            --dark-space: #1a1a2e;
            --deep-blue: #16213e;
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.36);
            --gradient-tech: linear-gradient(135deg, var(--tech-cyan), var(--tech-blue), var(--tech-purple));
            --gradient-eco: linear-gradient(135deg, var(--tech-green), var(--tech-cyan));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--deep-blue) 0%, var(--dark-space) 50%, #0f3460 100%);
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(0, 245, 212, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(0, 204, 136, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(155, 93, 229, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: var(--glass-shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-tech);
        }

        .logo {
            font-size: 3rem;
            font-weight: 800;
            background: var(--gradient-eco);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-eco);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--glass-shadow), 0 0 40px rgba(0, 245, 212, 0.2);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: var(--gradient-eco);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            text-shadow: 0 0 30px rgba(0, 245, 212, 0.3);
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            margin-bottom: 30px;
            box-shadow: var(--glass-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--glass-shadow), 0 0 50px rgba(0, 245, 212, 0.15);
        }

        .card-header {
            padding: 25px 30px;
            border-bottom: 1px solid var(--glass-border);
            background: rgba(255, 255, 255, 0.05);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--tech-cyan);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--tech-cyan);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .required::after {
            content: " *";
            color: var(--tech-pink);
        }

        input, textarea, select {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: white;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--tech-cyan);
            box-shadow: 0 0 25px rgba(0, 245, 212, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }

        input::placeholder, textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.5;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient-eco);
            color: var(--dark-space);
            box-shadow: 0 4px 20px rgba(0, 245, 212, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 245, 212, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--tech-cyan);
            border: 2px solid var(--tech-cyan);
        }

        .btn-secondary:hover {
            background: var(--tech-cyan);
            color: var(--dark-space);
        }

        .btn-warning {
            background: linear-gradient(45deg, var(--tech-yellow), #ff9e00);
            color: var(--dark-space);
        }

        .btn-danger {
            background: linear-gradient(45deg, var(--tech-pink), #e91e63);
            color: white;
        }

        .btn-sm {
            padding: 10px 18px;
            font-size: 0.9rem;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.02);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: transparent;
        }

        th {
            background: var(--gradient-eco);
            color: var(--dark-space);
            padding: 18px;
            text-align: left;
            font-weight: 700;
            font-size: 1rem;
        }

        td {
            padding: 18px;
            border-bottom: 1px solid var(--glass-border);
            color: rgba(255, 255, 255, 0.9);
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .message {
            padding: 18px;
            border-radius: 15px;
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid;
            backdrop-filter: blur(20px);
        }

        .message.success {
            background: rgba(0, 245, 212, 0.1);
            color: var(--tech-cyan);
            border-color: var(--tech-cyan);
        }

        .message.error {
            background: rgba(241, 91, 181, 0.1);
            color: var(--tech-pink);
            border-color: var(--tech-pink);
        }

        .message.warning {
            background: rgba(254, 228, 64, 0.1);
            color: var(--tech-yellow);
            border-color: var(--tech-yellow);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: rgba(255, 255, 255, 0.6);
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 25px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.9);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .logo {
                font-size: 2.2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-leaf"></i>
                EcoTrack
            </div>
            <div class="page-title">Gestion des Participations</div>
            <div class="page-subtitle">Backoffice Innovation Technologique</div>
        </div>
        
        <?php echo $message; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($participations); ?></div>
                <div class="stat-label">Participations Actives</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $participationManager->countParticipations(); ?></div>
                <div class="stat-label">En Base de Données</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo date('H:i'); ?></div>
                <div class="stat-label">Heure Actuelle</div>
            </div>
        </div>

        <!-- Formulaire CRUD -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-<?= $editingParticipation ? 'edit' : 'handshake' ?>"></i>
                    <?= $editingParticipation ? 'Modifier la Participation' : 'Nouvelle Participation' ?>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" id="participationForm">
                    <input type="hidden" name="action" value="<?= $editingParticipation ? 'edit_participation' : 'add_participation' ?>">
                    <?php if ($editingParticipation): ?>
                        <input type="hidden" name="idParticipation" value="<?= $editingParticipation['idParticipation'] ?>">
                        <input type="hidden" name="dateParticipation" value="<?= $editingParticipation['dateParticipation'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="idEvenement" class="required">
                            <i class="fas fa-calendar-check"></i>
                            ID Événement
                        </label>
                        <input type="number" id="idEvenement" name="idEvenement" required 
                               value="<?= $editingParticipation ? $editingParticipation['idEvenement'] : '' ?>"
                               min="1" placeholder="ID de l'événement concerné">
                    </div>
                    
                    <div class="form-group">
                        <label for="contenu" class="required">
                            <i class="fas fa-comments"></i>
                            Message de Participation
                        </label>
                        <textarea id="contenu" name="contenu" required 
                                  placeholder="Saisissez le message de participation..."><?= $editingParticipation ? htmlspecialchars($editingParticipation['contenu']) : '' ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?= $editingParticipation ? 'save' : 'paper-plane' ?>"></i>
                            <?= $editingParticipation ? 'Mettre à jour' : 'Créer la Participation' ?>
                        </button>
                        <?php if ($editingParticipation): ?>
                            <a href="Participation.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des participations -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-users"></i> 
                    Liste des Participations (<?php echo count($participations); ?>)
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($participations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h3>Aucune participation trouvée</h3>
                        <p>Commencez par ajouter votre première participation.</p>
                        <a href="#participationForm" class="btn btn-primary" style="margin-top: 25px;">
                            <i class="fas fa-plus"></i> Créer une Participation
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Événement</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($participations as $part): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($part['idParticipation']) ?></strong></td>
                                    <td>Événement #<?= htmlspecialchars($part['idEvenement']) ?></td>
                                    <td>
                                        <?= strlen($part['contenu']) > 80 ? 
                                            htmlspecialchars(substr($part['contenu'], 0, 80)) . '...' : 
                                            htmlspecialchars($part['contenu']) ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($part['dateParticipation'])) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="Participation.php?edit_id=<?= $part['idParticipation'] ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                            <a href="Participation.php?delete_id=<?= $part['idParticipation'] ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette participation ?');">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            });
        }, 5000);

        // Validation formulaire
        document.getElementById('participationForm').addEventListener('submit', function(e) {
            const idEvenement = document.getElementById('idEvenement').value.trim();
            const contenu = document.getElementById('contenu').value.trim();
            
            if (!idEvenement || idEvenement < 1) {
                e.preventDefault();
                alert('Veuillez saisir un ID d\'événement valide (supérieur à 0).');
                return;
            }
            
            if (!contenu || contenu.length < 10) {
                e.preventDefault();
                alert('Le message de participation doit contenir au moins 10 caractères.');
                return;
            }
        });
    </script>
</body>
</html>