<?php
require_once '../../controller/functions.php';
requireAdmin();

$message = '';
$messageType = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['ajouter'])) {
        $nom = $_POST['nom'];
        $type = $_POST['type'];
        $cout_moyen = floatval($_POST['cout_moyen']);
        $emission_carbone = floatval($_POST['emission_carbone']);
        $description = $_POST['description'];
        
        $result = ajouterSource($nom, $type, $cout_moyen, $emission_carbone, $description);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['modifier'])) {
        $id = intval($_POST['id']);
        $nom = $_POST['nom'];
        $type = $_POST['type'];
        $cout_moyen = floatval($_POST['cout_moyen']);
        $emission_carbone = floatval($_POST['emission_carbone']);
        $description = $_POST['description'];
        
        $result = modifierSource($id, $nom, $type, $cout_moyen, $emission_carbone, $description);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['supprimer'])) {
        $id = intval($_POST['id']);
        $result = supprimerSource($id);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
}

$sources = getAllSources();

// Édition
$editMode = false;
$editData = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $editData = getSourceById(intval($_GET['edit']));
}
?>

<?php renderHeader("Gestion des Sources"); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Gestion des Sources d'Énergie</h1>
        <p class="text-slate-500 mt-1">Ajouter, modifier ou supprimer les sources</p>
    </div>
    <a href="dashboard.php" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4 inline mr-2"></i>Retour
    </a>
</div>

<?php if ($message): ?>
<div class="mb-4 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
    <strong><?php echo $messageType == 'success' ? '✓ ' : '✗ '; ?></strong>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Formulaire -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6 sticky top-24">
            <h2 class="text-xl font-bold text-slate-800 mb-6">
                <i data-lucide="<?php echo $editMode ? 'edit' : 'plus'; ?>" class="w-5 h-5 inline mr-2 text-green-600"></i>
                <?php echo $editMode ? 'Modifier' : 'Ajouter'; ?> une Source
            </h2>
            
            <form method="POST" class="space-y-4">
                <?php if ($editMode): ?>
                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Nom de la source *</label>
                    <input type="text" name="nom" required value="<?php echo $editMode ? htmlspecialchars($editData['nom']) : ''; ?>" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 p-3" placeholder="Ex: Solaire Photovoltaïque">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Type *</label>
                    <select name="type" required class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 p-3">
                        <option value="">Choisir...</option>
                        <option value="Renouvelable" <?php echo ($editMode && $editData['type'] == 'Renouvelable') ? 'selected' : ''; ?>>Renouvelable</option>
                        <option value="Non-Renouvelable" <?php echo ($editMode && $editData['type'] == 'Non-Renouvelable') ? 'selected' : ''; ?>>Non-Renouvelable</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Coût moyen (€/kWh) *</label>
                    <input type="number" name="cout_moyen" step="0.01" required value="<?php echo $editMode ? $editData['cout_moyen'] : ''; ?>" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 p-3" placeholder="0.10">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Émission CO2 (kg/kWh) *</label>
                    <input type="number" name="emission_carbone" step="0.01" required value="<?php echo $editMode ? $editData['emission_carbone'] : ''; ?>" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 p-3" placeholder="0.05">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-green-500 focus:border-green-500 p-3" placeholder="Description de la source..."><?php echo $editMode ? htmlspecialchars($editData['description']) : ''; ?></textarea>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" name="<?php echo $editMode ? 'modifier' : 'ajouter'; ?>" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-green-600 hover:to-green-700 transition-all">
                        <i data-lucide="check" class="inline w-4 h-4 mr-2"></i>
                        <?php echo $editMode ? 'Modifier' : 'Ajouter'; ?>
                    </button>
                    <?php if ($editMode): ?>
                    <a href="manage_sources.php" class="flex-1 bg-slate-200 text-slate-700 font-semibold py-3 px-6 rounded-xl hover:bg-slate-300 transition-all text-center">
                        <i data-lucide="x" class="inline w-4 h-4 mr-2"></i>
                        Annuler
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Liste -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-xl font-bold text-slate-800">Sources Disponibles (<?php echo count($sources); ?>)</h2>
            </div>
            
            <div class="p-6 space-y-4">
                <?php if (empty($sources)): ?>
                <div class="text-center py-8 text-slate-500">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-slate-300"></i>
                    <p>Aucune source enregistrée</p>
                </div>
                <?php else: ?>
                <?php foreach($sources as $source): ?>
                <div class="border border-slate-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h3 class="font-bold text-slate-800 text-lg"><?php echo htmlspecialchars($source['nom']); ?></h3>
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full mt-1 <?php echo $source['type'] == 'Renouvelable' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'; ?>">
                                <?php echo $source['type']; ?>
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <a href="?edit=<?php echo $source['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Modifier">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" onsubmit="return confirm('Supprimer cette source ?');" class="inline">
                                <input type="hidden" name="id" value="<?php echo $source['id']; ?>">
                                <button type="submit" name="supprimer" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-slate-500">Coût moyen:</span>
                            <span class="font-semibold text-slate-800 ml-2"><?php echo formatNumber($source['cout_moyen'], 2); ?> €/kWh</span>
                        </div>
                        <div>
                            <span class="text-slate-500">Émission CO2:</span>
                            <span class="font-semibold text-slate-800 ml-2"><?php echo formatNumber($source['emission_carbone'], 2); ?> kg/kWh</span>
                        </div>
                    </div>
                    
                    <?php if (!empty($source['description'])): ?>
                    <p class="text-sm text-slate-600 mt-3 pt-3 border-t border-slate-100">
                        <?php echo htmlspecialchars($source['description']); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php renderFooter(); ?>
