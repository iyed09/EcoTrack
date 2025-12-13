<?php
// model/api.php - VERSION COMPLÈTE AVEC TOUTES LES FONCTIONNALITÉS

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Configuration de la base de données
$host = 'localhost';
$dbname = 'eco_track';
$username = 'root';
$password = '';

// Fonction pour créer la base de données et les tables
function createDatabaseIfNotExists($host, $dbname, $username, $password) {
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Créer la base de données
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        
        // Créer la table consommations
        $createTableConsommations = "CREATE TABLE IF NOT EXISTS consommations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            idUser VARCHAR(100) NOT NULL,
            typeEnergie VARCHAR(50) NOT NULL,
            quantite DECIMAL(10,2) NOT NULL,
            dateDebut DATE NOT NULL,
            dateFin DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($createTableConsommations);
        
        // Créer la table projets_virtuels
        $createTableProjets = "CREATE TABLE IF NOT EXISTS projets_virtuels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            idUser VARCHAR(100) NOT NULL,
            objectif_conso DECIMAL(10,2) NOT NULL COMMENT 'Objectif en kWh',
            production_totale DECIMAL(10,2) NOT NULL COMMENT 'Production atteinte',
            cout_total DECIMAL(10,2) NOT NULL COMMENT 'Coût en euros',
            espace_total DECIMAL(10,2) NOT NULL COMMENT 'Espace en m²',
            details_json TEXT NOT NULL COMMENT 'Configuration JSON',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($createTableProjets);
        
        return $pdo;
    } catch(PDOException $e) {
        die(json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]));
    }
}

// Créer/vérifier la base de données et se connecter
$pdo = createDatabaseIfNotExists($host, $dbname, $username, $password);

// Récupération de l'action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Router les actions
switch($action) {
    // ========== AUTHENTIFICATION ==========
    case 'adminLogin':
        adminLogin();
        break;
    
    // ========== CONSOMMATIONS (FRONTOFFICE 1) ==========
    case 'getHistorique':
        getHistorique($pdo);
        break;
    
    case 'getLastConsommation':
        getLastConsommation($pdo);
        break;
    
    case 'addConsommation':
        addConsommation($pdo);
        break;
    
    // ========== ADMIN CONSOMMATIONS (BACKOFFICE 1) ==========
    case 'getAllConsommations':
        getAllConsommations($pdo);
        break;
    
    case 'getConsommationById':
        getConsommationById($pdo);
        break;
    
    case 'updateConsommation':
        updateConsommation($pdo);
        break;
    
    case 'deleteConsommation':
        deleteConsommation($pdo);
        break;
    
    case 'deleteMultiple':
        deleteMultiple($pdo);
        break;
    
    // ========== PROJETS VIRTUELS (FRONTOFFICE 2) ==========
    case 'saveProject':
        saveProject($pdo);
        break;
    
    // ========== ADMIN PROJETS (BACKOFFICE 2) ==========
    case 'getAllProjects':
        getAllProjects($pdo);
        break;
    
    case 'getProjectById':
        getProjectById($pdo);
        break;
    
    case 'updateProject':
        updateProject($pdo);
        break;
    
    case 'deleteProject':
        deleteProject($pdo);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Action inconnue: ' . $action]);
        break;
}

// ==================== FONCTIONS D'AUTHENTIFICATION ====================

function adminLogin() {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Identifiants admin
    $admin_username = 'admin';
    $admin_password = '12345678';
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        echo json_encode(['success' => true, 'message' => 'Connexion réussie']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
    }
    exit;
}

// ==================== FONCTIONS CONSOMMATIONS (SITE PUBLIC) ====================

function getHistorique($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM consommations ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($data);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function getLastConsommation($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM consommations ORDER BY created_at DESC LIMIT 1");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aucune consommation']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function addConsommation($pdo) {
    try {
        $idUser = $_POST['idUser'] ?? '';
        $typeEnergie = $_POST['typeEnergie'] ?? '';
        $quantite = $_POST['quantite'] ?? '';
        $dateDebut = $_POST['dateDebut'] ?? '';
        $dateFin = $_POST['dateFin'] ?? '';
        
        if(empty($idUser) || empty($typeEnergie) || empty($quantite) || empty($dateDebut) || empty($dateFin)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
            exit;
        }
        
        if (strtotime($dateFin) < strtotime($dateDebut)) {
            echo json_encode(['success' => false, 'message' => 'Date fin >= date début']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO consommations (idUser, typeEnergie, quantite, dateDebut, dateFin) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$idUser, $typeEnergie, $quantite, $dateDebut, $dateFin]);
        
        echo json_encode(['success' => true, 'message' => 'Ajouté!', 'id' => $pdo->lastInsertId()]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ==================== FONCTIONS ADMIN CONSOMMATIONS ====================

function getAllConsommations($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM consommations ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function getConsommationById($pdo) {
    try {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM consommations WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Introuvable']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function updateConsommation($pdo) {
    try {
        $id = $_POST['id'] ?? 0;
        $idUser = $_POST['idUser'] ?? '';
        $typeEnergie = $_POST['typeEnergie'] ?? '';
        $quantite = $_POST['quantite'] ?? '';
        $dateDebut = $_POST['dateDebut'] ?? '';
        $dateFin = $_POST['dateFin'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE consommations SET idUser=?, typeEnergie=?, quantite=?, dateDebut=?, dateFin=? WHERE id=?");
        $stmt->execute([$idUser, $typeEnergie, $quantite, $dateDebut, $dateFin, $id]);
        
        echo json_encode(['success' => true, 'message' => 'Mise à jour réussie']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function deleteConsommation($pdo) {
    try {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM consommations WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Suppression réussie']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function deleteMultiple($pdo) {
    try {
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        
        if(empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'Aucun ID fourni']);
            exit;
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM consommations WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        
        echo json_encode(['success' => true, 'message' => count($ids) . ' suppression(s) réussie(s)']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ==================== FONCTIONS PROJETS VIRTUELS ====================

function saveProject($pdo) {
    try {
        $idUser = $_POST['idUser'] ?? '';
        $objectif = $_POST['objectif'] ?? 0;
        $production = $_POST['production'] ?? 0;
        $cout = $_POST['cout'] ?? 0;
        $espace = $_POST['espace'] ?? 0;
        $details = $_POST['details'] ?? '{}';

        if(empty($idUser)) {
            echo json_encode(['success' => false, 'message' => 'ID Utilisateur requis']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO projets_virtuels (idUser, objectif_conso, production_totale, cout_total, espace_total, details_json) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$idUser, $objectif, $production, $cout, $espace, $details]);

        echo json_encode(['success' => true, 'message' => 'Projet sauvegardé avec succès !', 'id' => $pdo->lastInsertId()]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function getAllProjects($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM projets_virtuels ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function getProjectById($pdo) {
    try {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM projets_virtuels WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Projet introuvable']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function updateProject($pdo) {
    try {
        $id = $_POST['id'] ?? 0;
        $idUser = $_POST['idUser'] ?? '';
        $objectif = $_POST['objectif'] ?? 0;
        $production = $_POST['production'] ?? 0;
        $cout = $_POST['cout'] ?? 0;
        $espace = $_POST['espace'] ?? 0;
        $details = $_POST['details'] ?? '{}';

        $stmt = $pdo->prepare("UPDATE projets_virtuels SET idUser=?, objectif_conso=?, production_totale=?, cout_total=?, espace_total=?, details_json=? WHERE id=?");
        $stmt->execute([$idUser, $objectif, $production, $cout, $espace, $details, $id]);

        echo json_encode(['success' => true, 'message' => 'Projet mis à jour avec succès !']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function deleteProject($pdo) {
    try {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM projets_virtuels WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Projet supprimé avec succès !']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

?>
