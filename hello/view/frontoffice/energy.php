<?php 
require_once '../../controller/functions.php';

// Traitement POST
$message = '';
$messageType = '';
$showResultLabel = false;
$consumptionLevel = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_consommation'])) {
    $source_id = intval($_POST['source_id']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $consommation = floatval($_POST['consommation']);
    $utilisateur = $_POST['utilisateur'] ?? 'Utilisateur';
    
    $result = ajouterConsommation($source_id, $date_debut, $date_fin, $consommation, $utilisateur);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
    if ($result['success']) {
        $showResultLabel = true;
        $consumptionLevel = calculateLevel($consommation);
    }
}

$sources = getAllSources();
$consommations = getAllConsommations(50);
?>

<?php renderHeader("Consommation"); ?>

<!-- Label de résultat de consommation -->
<?php if ($showResultLabel && $consumptionLevel): ?>
<div id="consumption-result-label" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" style="display: flex;">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 transform transition-all animate-fade-in">
        <div class="text-center">
            <div class="text-6xl mb-4">
                <?php 
                if ($consumptionLevel['niveau'] == 'Faible') {
                    echo '✅';
                } elseif ($consumptionLevel['niveau'] == 'Bonne') {
                    echo '👍';
                } elseif ($consumptionLevel['niveau'] == 'Moyenne') {
                    echo '⚠️';
                } else {
                    echo '🔴';
                }
                ?>
            </div>
            <h2 class="text-3xl font-bold text-slate-800 mb-2">État de Consommation</h2>
            <div class="inline-flex px-6 py-3 rounded-full text-lg font-semibold mb-4 <?php echo $consumptionLevel['class']; ?> border-2">
                <?php echo $consumptionLevel['niveau']; ?>
            </div>
            <p class="text-slate-600 mb-6">
                <?php 
                if ($consumptionLevel['niveau'] == 'Faible') {
                    echo 'Excellent ! Votre consommation est faible. Continuez ainsi !';
                } elseif ($consumptionLevel['niveau'] == 'Bonne') {
                    echo 'Très bien ! Votre consommation est bonne. Vous êtes sur la bonne voie !';
                } elseif ($consumptionLevel['niveau'] == 'Moyenne') {
                    echo 'Votre consommation est moyenne. Vous pouvez encore l\'optimiser.';
                } else {
                    echo 'Votre consommation est élevée. Pensez à réduire votre consommation énergétique.';
                }
                ?>
            </p>
            <button onclick="closeConsumptionLabel()" class="bg-gradient-to-r from-brand-500 to-brand-600 text-white font-semibold py-3 px-8 rounded-xl hover:from-brand-600 hover:to-brand-700 transition-all shadow-lg shadow-brand-500/30">
                Fermer
            </button>
        </div>
    </div>
</div>
<script>
function closeConsumptionLabel() {
    document.getElementById('consumption-result-label').style.display = 'none';
}
</script>
<?php endif; ?>

<?php if ($message): ?>
<div class="mb-4 p-4 rounded-lg <?php echo $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
    <strong><?php echo $messageType == 'success' ? '✓ ' : '✗ '; ?></strong>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<div class="flex flex-col md:flex-row gap-8">
    <!-- Formulaire d'ajout -->
    <div class="w-full md:w-1/3">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6 sticky top-24">
            <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <div class="bg-brand-100 p-2 rounded-lg">
                    <i data-lucide="plus" class="w-5 h-5 text-brand-600"></i>
                </div>
                Ajouter Consommation
            </h2>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Source d'Énergie *</label>
                    <div class="relative">
                        <select name="source_id" required class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3 pl-10 transition-all">
                            <option value="">Choisir une source...</option>
                            <?php foreach($sources as $source): ?>
                                <option value="<?php echo $source['id']; ?>">
                                    <?php echo htmlspecialchars($source['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="zap" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Date Début *</label>
                    <div class="relative">
                        <input type="date" name="date_debut" required value="<?php echo date('Y-m-d'); ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-3 pl-10 transition-all">
                        <i data-lucide="calendar" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Date Fin *</label>
                    <div class="relative">
                        <input type="date" name="date_fin" required value="<?php echo date('Y-m-d'); ?>" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-3 pl-10 transition-all">
                        <i data-lucide="calendar" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Consommation (kWh) *</label>
                    <div class="relative">
                        <input type="number" name="consommation" step="0.01" required min="0.01" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-3 pl-10 transition-all" placeholder="0.00">
                        <i data-lucide="activity" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">Utilisateur</label>
                    <div class="relative">
                        <input type="text" name="utilisateur" value="Utilisateur" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-3 pl-10 transition-all">
                        <i data-lucide="user" class="absolute left-3 top-3.5 w-4 h-4 text-slate-400"></i>
                    </div>
                </div>

                <button type="submit" name="ajouter_consommation" class="w-full bg-gradient-to-r from-brand-500 to-brand-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-brand-600 hover:to-brand-700 transition-all shadow-lg shadow-brand-500/30">
                    <i data-lucide="check" class="inline w-4 h-4 mr-2"></i>
                    Enregistrer
                </button>
            </form>
        </div>
    </div>

    <!-- Liste des consommations -->
    <div class="w-full md:w-2/3">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-6">
            <h2 class="text-xl font-bold text-slate-800 mb-6">Historique des Consommations</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase">
                        <tr>
                            <th class="p-3 text-left">Période</th>
                            <th class="p-3 text-left">Source</th>
                            <th class="p-3 text-right">Conso</th>
                            <th class="p-3 text-right">Coût</th>
                            <th class="p-3 text-right">Impact</th>
                            <th class="p-3 text-center">Niveau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($consommations)): ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500">
                                Aucune consommation enregistrée
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($consommations as $conso): 
                            $level = calculateLevel($conso['consommation']);
                        ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 text-sm text-slate-600">
                                <span class="font-medium"><?php echo date('d M', strtotime($conso['date_debut'])); ?></span>
                                <span class="text-slate-400 mx-1">→</span>
                                <span class="font-medium"><?php echo date('d M Y', strtotime($conso['date_fin'])); ?></span>
                            </td>
                            <td class="p-3 text-sm font-medium">
                                <?php echo htmlspecialchars($conso['source_nom']); ?>
                                <span class="block text-xs text-slate-500"><?php echo $conso['source_type']; ?></span>
                            </td>
                            <td class="p-3 text-sm text-right font-mono">
                                <?php echo formatNumber($conso['consommation'], 1); ?> kWh
                            </td>
                            <td class="p-3 text-sm text-right font-mono text-brand-600">
                                <?php echo formatNumber($conso['cout_total'], 2); ?> €
                            </td>
                            <td class="p-3 text-sm text-right font-mono">
                                <?php echo formatNumber($conso['emission_totale'], 2); ?> kg
                            </td>
                            <td class="p-3 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $level['class']; ?>">
                                    <?php echo $level['niveau']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php renderFooter(); ?>
