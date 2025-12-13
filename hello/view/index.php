<?php require_once '../controller/functions.php'; ?>
<?php renderHeader("Accueil"); ?>
<?php $stats = getMockStats(); ?>

<!-- Hero Section -->
<div class="relative rounded-3xl overflow-hidden bg-slate-900 text-white shadow-2xl mb-8 animate-fade-in group">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=2000&q=80" 
             alt="Eco City" 
             class="w-full h-full object-cover opacity-40 transition-transform duration-1000 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/80 to-transparent"></div>
    </div>
    
    <div class="relative p-8 md:p-12 max-w-3xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 border border-brand-500/30 text-brand-300 text-sm font-medium mb-6">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
            </span>
            Système actif v2.0
        </div>
        <h1 class="text-4xl md:text-5xl font-heading font-bold mb-4 leading-tight">
            Bienvenue sur <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-teal-400">EcoTrack</span>
        </h1>
        <p class="text-slate-300 text-lg mb-8 leading-relaxed">
            Votre plateforme centralisée pour la surveillance, l'analyse et l'optimisation de votre empreinte énergétique. 
            Prenez des décisions éclairées grâce à nos données en temps réel.
        </p>
        <div class="flex flex-wrap gap-4">
            <a href="frontoffice/energy.php" class="px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl transition-all shadow-lg shadow-brand-600/30 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Saisir Consommation
            </a>
            <a href="frontoffice/chatbot.php" class="px-6 py-3 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/10 text-white font-semibold rounded-xl transition-all flex items-center gap-2">
                <i data-lucide="message-square" class="w-5 h-5"></i>
                Demander à l'IA
            </a>
        </div>
    </div>
</div>

<!-- KPI Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover-elevate">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                <i data-lucide="zap" class="w-6 h-6"></i>
            </div>
            <span class="flex items-center text-xs font-medium text-red-500 bg-red-50 px-2 py-1 rounded-full">
                <i data-lucide="trending-up" class="w-3 h-3 mr-1"></i> +4.5%
            </span>
        </div>
        <div class="text-3xl font-bold text-slate-800 mb-1"><?php echo formatNumber($stats['total_consommation']); ?></div>
        <div class="text-sm text-slate-500 font-medium">kWh Consommés</div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover-elevate">
        <div class="flex justify-between items-start mb-4">
            <div class="p-3 bg-brand-50 rounded-xl text-brand-600">
                <i data-lucide="leaf" class="w-6 h-6"></i>
            </div>
            <span class="flex items-center text-xs font-medium text-brand-600 bg-brand-50 px-2 py-1 rounded-full">
                <i data-lucide="trending-down" class="w-3 h-3 mr-1"></i> -12%
            </span>
        </div>
        <div class="text-3xl font-bold text-slate-800 mb-1"><?php echo formatNumber($stats['total_emissions']); ?></div>
        <div class="text-sm text-slate-500 font-medium">kg CO2 Émis</div>
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
        <div class="text-sm text-slate-500 font-medium">Sources Actives</div>
    </div>
</div>

<!-- Analytics Preview -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
            <i data-lucide="pie-chart" class="w-5 h-5 text-slate-400"></i>
            Répartition Énergétique
        </h3>
        <div class="h-[300px] flex items-center justify-center">
            <canvas id="distributionChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
            <i data-lucide="bar-chart-2" class="w-5 h-5 text-slate-400"></i>
            Consommation Hebdomadaire
        </h3>
        <div class="h-[300px] flex items-center justify-center">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx1 = document.getElementById('distributionChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Solaire', 'Éolien', 'Gaz', 'Nucléaire'],
            datasets: [{
                data: [35, 25, 15, 25],
                backgroundColor: ['#22c55e', '#34d399', '#f59e0b', '#6366f1'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const ctx2 = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            datasets: [{
                label: 'kWh',
                data: [65, 59, 80, 81, 56, 40, 45],
                backgroundColor: '#22c55e',
                borderRadius: 6
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php renderFooter(); ?>
