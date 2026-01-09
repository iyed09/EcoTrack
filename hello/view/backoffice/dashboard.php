<?php
require_once '../../controller/functions.php';
requireAdmin();

$stats = getGlobalStats();
?>

<?php renderHeader("Dashboard Admin"); ?>

<div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
            <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
        </div>
        <div>
            <h2 class="font-bold text-slate-800">Espace Administration</h2>
            <p class="text-sm text-slate-600">Bienvenue <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover-elevate">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                <i data-lucide="zap" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-slate-800 mb-1"><?php echo formatNumber($stats['total_consommation']); ?></div>
        <div class="text-sm text-slate-500 font-medium">kWh Total</div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover-elevate">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-green-50 rounded-xl text-green-600">
                <i data-lucide="leaf" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-slate-800 mb-1"><?php echo formatNumber($stats['total_emissions']); ?></div>
        <div class="text-sm text-slate-500 font-medium">kg CO2</div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover-elevate">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-purple-50 rounded-xl text-purple-600">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-slate-800 mb-1"><?php echo formatNumber($stats['total_cout']); ?> €</div>
        <div class="text-sm text-slate-500 font-medium">Coût Total</div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover-elevate">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-orange-50 rounded-xl text-orange-600">
                <i data-lucide="lightbulb" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="text-3xl font-bold text-slate-800 mb-1"><?php echo $stats['total_sources']; ?></div>
        <div class="text-sm text-slate-500 font-medium">Sources</div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <a href="manage_energy.php" class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover-elevate block">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center">
                <i data-lucide="database" class="w-7 h-7 text-blue-600"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-800">Gérer les Consommations</h3>
                <p class="text-slate-500 text-sm">Modifier, supprimer les données</p>
            </div>
        </div>
        <div class="flex items-center text-blue-600 font-semibold">
            Accéder <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
        </div>
    </a>

    <a href="manage_sources.php" class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover-elevate block">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-14 h-14 bg-green-50 rounded-xl flex items-center justify-center">
                <i data-lucide="layers" class="w-7 h-7 text-green-600"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-800">Gérer les Sources</h3>
                <p class="text-slate-500 text-sm">Ajouter, modifier, supprimer</p>
            </div>
        </div>
        <div class="flex items-center text-green-600 font-semibold">
            Accéder <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
        </div>
    </a>
</div>

<?php renderFooter(); ?>
