<?php
require_once __DIR__ . '/../model/database.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================================
   AUTHENTIFICATION ADMIN
   ========================================== */

function loginAdmin($username, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return true;
    }
    return false;
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function logoutAdmin() {
    session_destroy();
    header('Location: login.php');
    exit();
}

/* ==========================================
   MOCK DATA GENERATORS
   ========================================== */

function getMockSources() {
    return [
        ['id' => 1, 'nom' => 'Solaire Photovoltaïque', 'type' => 'Renouvelable', 'cout_moyen' => 0.10, 'emission_carbone' => 0.05, 'description' => 'Panneaux solaires haute efficacité'],
        ['id' => 2, 'nom' => 'Éolien Offshore', 'type' => 'Renouvelable', 'cout_moyen' => 0.08, 'emission_carbone' => 0.03, 'description' => 'Parc éolien en mer'],
        ['id' => 3, 'nom' => 'Gaz Naturel', 'type' => 'Non-Renouvelable', 'cout_moyen' => 0.15, 'emission_carbone' => 0.45, 'description' => 'Centrale à cycle combiné'],
        ['id' => 4, 'nom' => 'Nucléaire', 'type' => 'Non-Renouvelable', 'cout_moyen' => 0.09, 'emission_carbone' => 0.01, 'description' => 'Centrale nucléaire'],
        ['id' => 5, 'nom' => 'Biomasse', 'type' => 'Renouvelable', 'cout_moyen' => 0.11, 'emission_carbone' => 0.18, 'description' => 'Valorisation des déchets organiques']
    ];
}

function getMockStats() {
    return [
        'total_consommation' => 15420.50,
        'total_cout' => 2340.80,
        'total_emissions' => 850.25,
        'total_sources' => 5,
        'moyenne_consommation' => 340.5
    ];
}

/* ==========================================
   GESTION DES SOURCES D'ÉNERGIE
   ========================================== */

function getAllSources() {
    $conn = getDBConnection();
    
    if ($conn->query("SHOW TABLES LIKE 'source_energie'")->num_rows == 0) {
        return getMockSources();
    }
    
    $sql = "SELECT * FROM source_energie ORDER BY nom ASC";
    $result = $conn->query($sql);
    $sources = [];
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $sources[] = $row;
        }
    }
    
    return $sources;
}

function getSourceById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM source_energie WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function ajouterSource($nom, $type, $cout_moyen, $emission_carbone, $description = '') {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO source_energie (nom, type, cout_moyen, emission_carbone, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdds", $nom, $type, $cout_moyen, $emission_carbone, $description);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Source ajoutée avec succès !'];
    } else {
        return ['success' => false, 'message' => 'Erreur: ' . $stmt->error];
    }
}

function modifierSource($id, $nom, $type, $cout_moyen, $emission_carbone, $description = '') {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE source_energie SET nom = ?, type = ?, cout_moyen = ?, emission_carbone = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssddsi", $nom, $type, $cout_moyen, $emission_carbone, $description, $id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Source modifiée avec succès !'];
    } else {
        return ['success' => false, 'message' => 'Erreur: ' . $stmt->error];
    }
}

function supprimerSource($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM source_energie WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Source supprimée avec succès !'];
    } else {
        return ['success' => false, 'message' => 'Erreur: Cette source est utilisée dans des consommations'];
    }
}

/* ==========================================
   GESTION DES CONSOMMATIONS
   ========================================== */

function ajouterConsommation($source_id, $date_debut, $date_fin, $consommation, $utilisateur = 'Utilisateur') {
    $conn = getDBConnection();
    
    // Récupérer les infos de la source
    $stmt = $conn->prepare("SELECT cout_moyen, emission_carbone FROM source_energie WHERE id = ?");
    $stmt->bind_param("i", $source_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return ['success' => false, 'message' => 'Source introuvable'];
    }
    
    $source = $result->fetch_assoc();
    $cout_total = $consommation * $source['cout_moyen'];
    $emission_totale = $consommation * $source['emission_carbone'];
    
    // Insérer la consommation avec date_debut et date_fin
    $stmt = $conn->prepare("INSERT INTO energie (source_energie_id, date_debut, date_fin, consommation, cout_total, emission_totale, utilisateur) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issddds", $source_id, $date_debut, $date_fin, $consommation, $cout_total, $emission_totale, $utilisateur);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Consommation ajoutée avec succès !'];
    } else {
        return ['success' => false, 'message' => 'Erreur: ' . $stmt->error];
    }
}

function getAllConsommations($limit = 100) {
    $conn = getDBConnection();
    
    $sql = "SELECT 
                e.id,
                e.date_debut,
                e.date_fin,
                e.consommation,
                e.cout_total,
                e.emission_totale,
                e.utilisateur,
                e.statut,
                s.nom AS source_nom,
                s.type AS source_type,
                s.id AS source_id
            FROM energie e
            INNER JOIN source_energie s ON e.source_energie_id = s.id
            ORDER BY e.date_debut DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $consommations = [];
    while ($row = $result->fetch_assoc()) {
        $consommations[] = $row;
    }
    
    return $consommations;
}

function getConsommationById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT e.*, s.nom AS source_nom FROM energie e INNER JOIN source_energie s ON e.source_energie_id = s.id WHERE e.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function modifierConsommation($id, $source_id, $date_debut, $date_fin, $consommation, $utilisateur, $statut) {
    $conn = getDBConnection();
    
    // Récupérer les infos de la source
    $stmt = $conn->prepare("SELECT cout_moyen, emission_carbone FROM source_energie WHERE id = ?");
    $stmt->bind_param("i", $source_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $source = $result->fetch_assoc();
    
    $cout_total = $consommation * $source['cout_moyen'];
    $emission_totale = $consommation * $source['emission_carbone'];
    
    $stmt = $conn->prepare("UPDATE energie SET source_energie_id = ?, date_debut = ?, date_fin = ?, consommation = ?, cout_total = ?, emission_totale = ?, utilisateur = ?, statut = ? WHERE id = ?");
    $stmt->bind_param("issdddssi", $source_id, $date_debut, $date_fin, $consommation, $cout_total, $emission_totale, $utilisateur, $statut, $id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Consommation modifiée avec succès !'];
    } else {
        return ['success' => false, 'message' => 'Erreur: ' . $stmt->error];
    }
}

function supprimerConsommation($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM energie WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Consommation supprimée avec succès !'];
    } else {
        return ['success' => false, 'message' => 'Erreur: ' . $stmt->error];
    }
}

/* ==========================================
   STATISTIQUES
   ========================================== */

function getGlobalStats() {
    $conn = getDBConnection();
    
    if ($conn->query("SHOW TABLES LIKE 'energie'")->num_rows == 0) {
        return getMockStats();
    }
    
    $sql = "SELECT 
            COUNT(*) as total_records,
            COALESCE(SUM(consommation), 0) as total_consommation,
            COALESCE(SUM(emission_totale), 0) as total_emissions,
            COALESCE(SUM(cout_total), 0) as total_cout
            FROM energie";
    
    $result = $conn->query($sql);
    $stats = $result->fetch_assoc();
    
    $countResult = $conn->query("SELECT COUNT(*) as cnt FROM source_energie");
    if ($countResult) {
        $stats['total_sources'] = $countResult->fetch_assoc()['cnt'];
    } else {
        $stats['total_sources'] = 0;
    }
    
    return $stats;
}

/* ==========================================
   HELPERS
   ========================================== */

function calculateLevel($consommation) {
    if ($consommation < 30) {
        return ['niveau' => 'Faible', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200'];
    } elseif ($consommation < 80) {
        return ['niveau' => 'Bonne', 'class' => 'bg-green-100 text-green-800 border-green-200'];
    } elseif ($consommation < 150) {
        return ['niveau' => 'Moyenne', 'class' => 'bg-yellow-100 text-yellow-800 border-yellow-200'];
    } else {
        return ['niveau' => 'Élevée', 'class' => 'bg-red-100 text-red-800 border-red-200'];
    }
}

function formatNumber($number, $decimals = 2) {
    return number_format((float)$number, $decimals, ',', ' ');
}

/* ==========================================
   HTML RENDERING
   ========================================== */

function renderHeader($title = "EcoTrack") {
    $rootPath = (strpos($_SERVER['SCRIPT_NAME'], 'frontoffice') !== false || strpos($_SERVER['SCRIPT_NAME'], 'backoffice') !== false) ? '../' : '';
    $isAdmin = isAdminLoggedIn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - EcoTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#e6f7ed',
                            100: '#b3e6cf',
                            200: '#80d5b1',
                            300: '#4dc593',
                            400: '#1ab475',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .hover-elevate { transition: all 0.3s ease; }
        .hover-elevate:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }
        @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fade-in 0.5s ease-out; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-slate-50 font-sans">
    <nav class="sticky top-0 z-50 bg-white border-b border-slate-200 shadow-sm backdrop-blur-lg bg-white/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-brand-500 to-brand-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="leaf" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="text-xl font-bold text-slate-800">EcoTrack</span>
                </div>
                
                <div class="hidden md:flex items-center space-x-1">
                    <a href="<?php echo $rootPath; ?>index.php" class="px-4 py-2 text-slate-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors">
                        <i data-lucide="home" class="w-4 h-4 inline mr-2"></i>Accueil
                    </a>
                    <a href="<?php echo $rootPath; ?>frontoffice/energy.php" class="px-4 py-2 text-slate-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors">
                        <i data-lucide="zap" class="w-4 h-4 inline mr-2"></i>Consommation
                    </a>
                    <a href="<?php echo $rootPath; ?>frontoffice/source_energie.php" class="px-4 py-2 text-slate-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors">
                        <i data-lucide="layers" class="w-4 h-4 inline mr-2"></i>Sources
                    </a>
                    <a href="<?php echo $rootPath; ?>frontoffice/project.php" class="px-4 py-2 text-slate-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors">
                        <i data-lucide="rocket" class="w-4 h-4 inline mr-2"></i>Project
                    </a>
                    <a href="<?php echo $rootPath; ?>frontoffice/chatbot.php" class="px-4 py-2 text-slate-600 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors">
                        <i data-lucide="message-square" class="w-4 h-4 inline mr-2"></i>Chatbot
                    </a>
                    
                    <?php if ($isAdmin): ?>
                    <a href="<?php echo $rootPath; ?>backoffice/dashboard.php" class="px-4 py-2 bg-red-500 text-white hover:bg-red-600 rounded-lg transition-colors font-semibold">
                        <i data-lucide="shield" class="w-4 h-4 inline mr-2"></i>Admin
                    </a>
                    <a href="<?php echo $rootPath; ?>backoffice/logout.php" class="px-4 py-2 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4 inline mr-2"></i>Déconnexion
                    </a>
                    <?php else: ?>
                    <a href="<?php echo $rootPath; ?>backoffice/login.php" class="px-4 py-2 bg-red-500 text-white hover:bg-red-600 rounded-lg transition-colors font-semibold">
                        <i data-lucide="shield" class="w-4 h-4 inline mr-2"></i>Admin
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
<?php
}

function renderFooter() {
?>
    </main>
    
    <footer class="bg-white border-t border-slate-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 py-8 text-center text-slate-500 text-sm">
            <p>© 2025 EcoTrack - Tous droits réservés</p>
        </div>
    </footer>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
<?php
}
?>
