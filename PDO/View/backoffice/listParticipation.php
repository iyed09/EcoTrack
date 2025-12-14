<?php
// listParticipation.php - Liste des participations
session_start();

// CONNEXION À LA BASE DE DONNÉES
$host = 'localhost';
$dbname = 'smartinnovators';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // RÉCUPÉRER LES PARTICIPATIONS
    $query = "SELECT p.*, e.titre as event_titre 
              FROM participation p
              LEFT JOIN evenement e ON p.idEvenement = e.idEvenement
              ORDER BY p.dateParticipation DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $participations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Message de succès
    $message = '';
    $messageType = '';
    if (isset($_GET['message'])) {
        $message = htmlspecialchars($_GET['message']);
        $messageType = strpos($message, 'succes') !== false ? 'success' : 'error';
    }
    
} catch(PDOException $e) {
    $participations = [];
    $message = 'Erreur de connexion à la base de données: ' . $e->getMessage();
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Liste des Participations - EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2ecc71;
            --primary-light: rgba(46, 204, 113, 0.1);
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --accent: #9b59b6;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --dark: #1a252f;
            --light: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--gray-50) 0%, #f0f7ff 100%);
            color: var(--gray-800);
            line-height: 1.5;
            font-size: 0.875rem;
            min-height: 100vh;
            padding: 2rem;
        }

        /* LOGO */
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: var(--shadow);
        }

        .logo-text h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.25rem;
        }

        .logo-text span {
            font-size: 0.875rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        /* CONTAINER */
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* MESSAGES */
        .notification {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease-out;
        }

        .notification.success {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .notification.error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 1.5rem 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        /* BOUTONS */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #e67e22);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #c0392b);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }

        /* TABLEAU */
        .table-container {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead {
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            border-bottom: 2px solid var(--gray-200);
        }

        .table th {
            padding: 1.25rem 1.5rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .table td {
            padding: 1.5rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-700);
            font-size: 0.875rem;
            line-height: 1.6;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background: var(--primary-light);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* BADGES */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .badge-id {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary);
        }

        .badge-event {
            background: rgba(155, 89, 182, 0.1);
            color: var(--accent);
        }

        /* CONTENU */
        .contenu-cell {
            max-width: 300px;
        }

        .contenu-preview {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ACTIONS */
        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* ÉTAT VIDE */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 3.5rem;
            color: var(--gray-300);
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.75rem;
        }

        .empty-state p {
            max-width: 400px;
            margin: 0 auto 2rem;
            line-height: 1.6;
            color: var(--gray-500);
        }

        /* ANIMATIONS */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .header {
                flex-direction: column;
                gap: 1.5rem;
                align-items: stretch;
                text-align: center;
            }
            
            .page-title {
                justify-content: center;
            }
            
            .action-buttons {
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .table-container {
                border-radius: var(--radius);
                overflow-x: auto;
            }
            
            .table {
                min-width: 800px;
            }
            
            .table-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- LOGO -->
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-leaf"></i>
            </div>
            <div class="logo-text">
                <h1>EcoTrack</h1>
                <span>Gestion des Participations</span>
            </div>
        </div>

        <!-- MESSAGES -->
        <?php if (!empty($message)): ?>
            <div class="notification <?= $messageType ?>">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- HEADER -->
        <div class="header">
            <div class="page-title">
                <i class="fas fa-list-ol"></i>
                Liste des Participations
            </div>
            <div class="action-buttons">
                <a href="AddParticipation.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    Nouvelle Participation
                </a>
            </div>
        </div>

        <!-- TABLEAU DES PARTICIPATIONS -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-calendar-check"></i> Événement</th>
                        <th><i class="fas fa-comment-dots"></i> Contenu</th>
                        <th><i class="fas fa-clock"></i> Date</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participations)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-users-slash"></i>
                                    <h3>Aucune participation trouvée</h3>
                                    <p>Commencez par créer votre première participation</p>
                                    <a href="AddParticipation.php" class="btn btn-primary">
                                        <i class="fas fa-plus-circle"></i>
                                        Créer une Participation
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($participations as $participation): ?>
                            <?php 
                            $idParticipation = $participation['idParticipation'] ?? 'N/A';
                            $eventTitre = $participation['event_titre'] ?? 'Sans titre';
                            $contenu = $participation['contenu'] ?? '';
                            $dateParticipation = $participation['dateParticipation'] ?? date('Y-m-d H:i:s');
                            
                            // Limiter le contenu à 100 caractères pour l'affichage
                            $safeContenu = htmlspecialchars($contenu);
                            $contenuPreview = strlen($safeContenu) > 100 
                                ? substr($safeContenu, 0, 100) . '...' 
                                : $safeContenu;
                            
                            // Formater la date
                            $formattedDate = date('d/m/Y', strtotime($dateParticipation));
                            $formattedTime = date('H:i', strtotime($dateParticipation));
                            ?>
                            <tr>
                                <td>
                                    <span class="badge badge-id">
                                        <i class="fas fa-hashtag"></i>
                                        #<?= htmlspecialchars($idParticipation) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-event">
                                        <i class="fas fa-calendar-check"></i>
                                        <?= htmlspecialchars($eventTitre) ?>
                                    </span>
                                </td>
                                <td class="contenu-cell">
                                    <div class="contenu-preview" title="<?= htmlspecialchars($contenu) ?>">
                                        <?= $contenuPreview ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div style="font-weight: 600; color: var(--gray-900);">
                                            <?= $formattedDate ?>
                                        </div>
                                        <div style="color: var(--gray-500); font-size: 0.875rem;">
                                            <?= $formattedTime ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="editParticipation.php?edit_id=<?= $idParticipation ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                            Modifier
                                        </a>
                                        <a href="deleteParticipation.php?id=<?= $idParticipation ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette participation ?')">
                                            <i class="fas fa-trash-alt"></i>
                                            Supprimer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- COMPTEUR -->
        <?php if (!empty($participations)): ?>
        <div style="text-align: center; margin-top: 2rem; color: var(--gray-500);">
            Total : <?= count($participations) ?> participation(s)
        </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss messages
            setTimeout(() => {
                const alerts = document.querySelectorAll('.notification');
                alerts.forEach(alert => {
                    alert.style.transition = 'all 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);

            // Confirmation de suppression
            const deleteButtons = document.querySelectorAll('.btn-danger');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cette participation ?')) {
                        e.preventDefault();
                    }
                });
            });

            // Afficher le contenu complet au survol
            const previewCells = document.querySelectorAll('.contenu-preview');
            previewCells.forEach(cell => {
                const fullContent = cell.getAttribute('title');
                if (fullContent) {
                    cell.addEventListener('mouseenter', function() {
                        // Créer un tooltip
                        const tooltip = document.createElement('div');
                        tooltip.textContent = fullContent;
                        tooltip.style.cssText = `
                            position: absolute;
                            background: var(--dark);
                            color: white;
                            padding: 0.75rem 1rem;
                            border-radius: var(--radius);
                            font-size: 0.875rem;
                            z-index: 1000;
                            max-width: 400px;
                            white-space: normal;
                            word-wrap: break-word;
                            box-shadow: var(--shadow-lg);
                        `;
                        
                        const rect = cell.getBoundingClientRect();
                        tooltip.style.top = (rect.top - 10) + 'px';
                        tooltip.style.left = rect.left + 'px';
                        
                        document.body.appendChild(tooltip);
                        cell._tooltip = tooltip;
                    });
                    
                    cell.addEventListener('mouseleave', function() {
                        if (cell._tooltip) {
                            cell._tooltip.remove();
                            cell._tooltip = null;
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>