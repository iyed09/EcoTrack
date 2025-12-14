<?php
require_once '../../controller/functions.php';
requireAdmin();

$message = '';
$messageType = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['supprimer'])) {
        $id = intval($_POST['id']);
        $result = supprimerConsommation($id);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } elseif (isset($_POST['modifier'])) {
        $id = intval($_POST['id']);
        $source_id = intval($_POST['source_id']);
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        $consommation = floatval($_POST['consommation']);
        $utilisateur = $_POST['utilisateur'];
        $statut = $_POST['statut'];
        
        $result = modifierConsommation($id, $source_id, $date_debut, $date_fin, $consommation, $utilisateur, $statut);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
}

// Récupérer les données
$consommations = getAllConsommations(200);
$sources = getAllSources();

// Édition
$editMode = false;
$editData = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $editData = getConsommationById(intval($_GET['edit']));
}
?>

<?php renderHeader("Gestion des Consommations"); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Gestion des Consommations</h1>
        <p class="text-slate-500 mt-1">Modifier ou supprimer les enregistrements</p>
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

<!-- Formulaire d'édition (si en mode édition) -->
<?php if ($editMode && $editData): ?>
<div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
    <h2 class="text-xl font-bold text-slate-800 mb-4">
        <i data-lucide="edit" class="w-5 h-5 inline mr-2 text-blue-600"></i>
        Modifier la Consommation #<?php echo $editData['id']; ?>
    </h2>
    
    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
        
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-2">Source d'Énergie *</label>
            <div class="relative">
                <select name="source_id" required class="w-full appearance-none bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3 pl-10">
                    <?php foreach($sources as $source): ?>
                        <option value="<?php echo $source['id']; ?>" <?php echo $editData['source_energie_id'] == $source['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($source['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <i data-lucide="zap" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-2">Consommation (kWh) *</label>
            <div class="relative">
                <input type="number" name="consommation" step="0.01" required value="<?php echo $editData['consommation']; ?>" class="w-full bg-white border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3 pl-10">
                <i data-lucide="activity" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-2">Date Début *</label>
            <div class="relative">
                <input type="date" name="date_debut" required value="<?php echo $editData['date_debut']; ?>" class="w-full bg-white border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3 pl-10">
                <i data-lucide="calendar" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-2">Date Fin *</label>
            <div class="relative">
                <input type="date" name="date_fin" required value="<?php echo $editData['date_fin']; ?>" class="w-full bg-white border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3 pl-10">
                <i data-lucide="calendar" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-2">Utilisateur</label>
            <div class="relative">
                <input type="text" name="utilisateur" value="<?php echo htmlspecialchars($editData['utilisateur']); ?>" class="w-full bg-white border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3 pl-10">
                <i data-lucide="user" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-2">Statut</label>
            <div class="relative">
                <select name="statut" class="w-full appearance-none bg-white border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 p-3 pl-10">
                    <option value="En attente" <?php echo $editData['statut'] == 'En attente' ? 'selected' : ''; ?>>En attente</option>
                    <option value="Validé" <?php echo $editData['statut'] == 'Validé' ? 'selected' : ''; ?>>Validé</option>
                    <option value="Rejeté" <?php echo $editData['statut'] == 'Rejeté' ? 'selected' : ''; ?>>Rejeté</option>
                </select>
                <i data-lucide="check-circle" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
            </div>
        </div>
        
        <div class="md:col-span-2 flex gap-3">
            <button type="submit" name="modifier" class="flex-1 bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition-all">
                <i data-lucide="check" class="inline w-4 h-4 mr-2"></i>
                Enregistrer les modifications
            </button>
            <a href="manage_energy.php" class="flex-1 bg-slate-200 text-slate-700 font-semibold py-3 px-6 rounded-xl hover:bg-slate-300 transition-all text-center">
                <i data-lucide="x" class="inline w-4 h-4 mr-2"></i>
                Annuler
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Liste des consommations -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
    <div class="p-6 border-b border-slate-100">
        <h2 class="text-xl font-bold text-slate-800">Liste des Consommations (<?php echo count($consommations); ?>)</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                <tr>
                    <th class="p-4 text-left">ID</th>
                    <th class="p-4 text-left">Période</th>
                    <th class="p-4 text-left">Source</th>
                    <th class="p-4 text-right">Consommation</th>
                    <th class="p-4 text-right">Coût</th>
                    <th class="p-4 text-center">Statut</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($consommations)): ?>
                <tr>
                    <td colspan="7" class="p-8 text-center text-slate-500">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-slate-300"></i>
                        <p>Aucune consommation enregistrée</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach($consommations as $conso): ?>
                <tr class="hover:bg-slate-50">
                    <td class="p-4 text-sm font-mono text-slate-600">#<?php echo $conso['id']; ?></td>
                    <td class="p-4 text-sm text-slate-600">
                        <div class="flex items-center gap-1">
                            <span class="font-medium"><?php echo date('d/m/Y', strtotime($conso['date_debut'])); ?></span>
                            <span class="text-slate-400">→</span>
                            <span class="font-medium"><?php echo date('d/m/Y', strtotime($conso['date_fin'])); ?></span>
                        </div>
                    </td>
                    <td class="p-4 text-sm">
                        <span class="font-medium text-slate-900"><?php echo htmlspecialchars($conso['source_nom']); ?></span>
                        <span class="block text-xs text-slate-500"><?php echo $conso['source_type']; ?></span>
                    </td>
                    <td class="p-4 text-sm text-right font-mono"><?php echo formatNumber($conso['consommation'], 1); ?> kWh</td>
                    <td class="p-4 text-sm text-right font-mono text-blue-600"><?php echo formatNumber($conso['cout_total'], 2); ?> €</td>
                    <td class="p-4 text-center">
                        <?php
                        $statusColors = [
                            'En attente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                            'Validé' => 'bg-green-100 text-green-800 border-green-200',
                            'Rejeté' => 'bg-red-100 text-red-800 border-red-200'
                        ];
                        $statusClass = $statusColors[$conso['statut']] ?? 'bg-slate-100 text-slate-800';
                        ?>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium border <?php echo $statusClass; ?>">
                            <?php echo $conso['statut']; ?>
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="?edit=<?php echo $conso['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Modifier">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette consommation ?');" class="inline">
                                <input type="hidden" name="id" value="<?php echo $conso['id']; ?>">
                                <button type="submit" name="supprimer" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php renderFooter(); ?>
