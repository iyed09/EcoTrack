<?php 
require_once '../../controller/functions.php';

$sources = getAllSources();

// Filtrage et recherche
$search = $_GET['search'] ?? '';
$filterType = $_GET['type'] ?? '';

if ($search || $filterType) {
    $sources = array_filter($sources, function($source) use ($search, $filterType) {
        $matchSearch = empty($search) || 
                       stripos($source['nom'], $search) !== false || 
                       stripos($source['description'], $search) !== false;
        $matchType = empty($filterType) || $source['type'] == $filterType;
        return $matchSearch && $matchType;
    });
}
?>

<?php renderHeader("Sources d'Énergie"); ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-800 mb-2">Sources d'Énergie</h1>
    <p class="text-slate-500">Découvrez les différentes sources d'énergie disponibles</p>
</div>

<!-- Barre de recherche et filtrage -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-8">
    <form method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <label class="block text-sm font-semibold text-slate-600 mb-2">Rechercher</label>
            <div class="relative">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Rechercher une source d'énergie..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 p-3 pl-10 transition-all">
                <i data-lucide="search" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
            </div>
        </div>
        
        <div class="w-full md:w-64">
            <label class="block text-sm font-semibold text-slate-600 mb-2">Filtrer par type</label>
            <div class="relative">
                <select name="type" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 p-3 pl-10 transition-all">
                    <option value="">Tous les types</option>
                    <option value="Renouvelable" <?php echo $filterType == 'Renouvelable' ? 'selected' : ''; ?>>Renouvelable</option>
                    <option value="Non-Renouvelable" <?php echo $filterType == 'Non-Renouvelable' ? 'selected' : ''; ?>>Non-Renouvelable</option>
                </select>
                <i data-lucide="filter" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
            </div>
        </div>
        
        <div class="flex items-end gap-2">
            <button type="submit" class="bg-gradient-to-r from-brand-500 to-brand-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-brand-600 hover:to-brand-700 transition-all shadow-lg shadow-brand-500/30">
                <i data-lucide="search" class="inline w-4 h-4 mr-2"></i>
                Rechercher
            </button>
            <?php if ($search || $filterType): ?>
            <a href="source_energie.php" class="bg-slate-100 text-slate-700 font-semibold py-3 px-6 rounded-xl hover:bg-slate-200 transition-all">
                <i data-lucide="x" class="inline w-4 h-4 mr-2"></i>
                Réinitialiser
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Résultats -->
<div class="mb-4 flex items-center justify-between">
    <p class="text-slate-600">
        <span class="font-semibold text-slate-800"><?php echo count($sources); ?></span> source(s) trouvée(s)
    </p>
    
    <?php if ($search || $filterType): ?>
    <div class="text-sm text-slate-500">
        <?php if ($search): ?>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-brand-100 text-brand-800 mr-2">
                <i data-lucide="search" class="w-3 h-3 mr-1"></i>
                "<?php echo htmlspecialchars($search); ?>"
            </span>
        <?php endif; ?>
        <?php if ($filterType): ?>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800">
                <i data-lucide="filter" class="w-3 h-3 mr-1"></i>
                <?php echo $filterType; ?>
            </span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Grille des sources -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($sources)): ?>
    <div class="col-span-full text-center py-12">
        <i data-lucide="inbox" class="w-16 h-16 mx-auto mb-4 text-slate-300"></i>
        <p class="text-slate-500 text-lg font-medium">Aucune source trouvée</p>
        <p class="text-slate-400 text-sm mt-2">Essayez de modifier vos critères de recherche</p>
    </div>
    <?php else: ?>
    <?php foreach($sources as $source): ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover-elevate overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center <?php echo $source['type'] == 'Renouvelable' ? 'bg-green-100' : 'bg-orange-100'; ?>">
                    <i data-lucide="<?php echo $source['type'] == 'Renouvelable' ? 'leaf' : 'flame'; ?>" class="w-6 h-6 <?php echo $source['type'] == 'Renouvelable' ? 'text-green-600' : 'text-orange-600'; ?>"></i>
                </div>
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full <?php echo $source['type'] == 'Renouvelable' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'; ?>">
                    <?php echo $source['type']; ?>
                </span>
            </div>
            
            <h3 class="text-xl font-bold text-slate-800 mb-2"><?php echo htmlspecialchars($source['nom']); ?></h3>
            
            <?php if (!empty($source['description'])): ?>
            <p class="text-sm text-slate-600 mb-4 line-clamp-2"><?php echo htmlspecialchars($source['description']); ?></p>
            <?php endif; ?>
            
            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                <div>
                    <div class="text-xs text-slate-500 mb-1">Coût moyen</div>
                    <div class="font-bold text-slate-800"><?php echo formatNumber($source['cout_moyen'], 2); ?> €<span class="text-xs font-normal text-slate-500">/kWh</span></div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 mb-1">Émission CO2</div>
                    <div class="font-bold text-slate-800"><?php echo formatNumber($source['emission_carbone'], 2); ?> kg<span class="text-xs font-normal text-slate-500">/kWh</span></div>
                </div>
            </div>
            
            <!-- Impact environnemental -->
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <?php 
                    $impactLevel = $source['emission_carbone'] < 0.1 ? 'Très faible' : 
                                   ($source['emission_carbone'] < 0.3 ? 'Faible' : 
                                   ($source['emission_carbone'] < 0.6 ? 'Moyen' : 'Élevé'));
                    $impactColor = $source['emission_carbone'] < 0.1 ? 'text-green-600' : 
                                   ($source['emission_carbone'] < 0.3 ? 'text-blue-600' : 
                                   ($source['emission_carbone'] < 0.6 ? 'text-orange-600' : 'text-red-600'));
                    ?>
                    <i data-lucide="leaf" class="w-4 h-4 <?php echo $impactColor; ?>"></i>
                    <span class="text-sm font-medium <?php echo $impactColor; ?>">Impact : <?php echo $impactLevel; ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php renderFooter(); ?>
