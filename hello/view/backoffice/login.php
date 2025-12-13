<?php
require_once '../../controller/functions.php';

// Si déjà connecté, rediriger vers le dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (loginAdmin($username, $password)) {
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Identifiants incorrects';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - EcoTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i data-lucide="shield" class="w-8 h-8 text-white"></i>
                </div>
            </div>
            
            <h2 class="text-2xl font-bold text-center text-slate-800 mb-2">Espace Administration</h2>
            <p class="text-center text-slate-500 mb-8">Connectez-vous pour accéder au backoffice</p>
            
            <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <i data-lucide="alert-circle" class="w-4 h-4 inline mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Nom d'utilisateur</label>
                    <div class="relative">
                        <input type="text" name="username" required class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block w-full p-3 pl-10 transition-all" placeholder="admin">
                        <i data-lucide="user" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Mot de passe</label>
                    <div class="relative">
                        <input type="password" name="password" required class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block w-full p-3 pl-10 transition-all" placeholder="••••••••">
                        <i data-lucide="lock" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-red-600 hover:to-red-700 transition-all shadow-lg shadow-red-500/30">
                    <i data-lucide="log-in" class="inline w-4 h-4 mr-2"></i>
                    Se connecter
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="../index.php" class="text-sm text-slate-500 hover:text-slate-700">
                    <i data-lucide="arrow-left" class="inline w-3 h-3 mr-1"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>
        
        <div class="mt-4 text-center text-slate-400 text-sm">
            <p>Compte par défaut : admin / 12345678</p>
        </div>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
