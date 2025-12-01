<?php 
require_once __DIR__.'/../../Controller/EvenementController.php';
$evenementC = new EvenementController();
$list = $evenementC->getAllEvenements();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Événements - EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .logo h1 {
            background: linear-gradient(45deg, #00d2b8, #00a896);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 3rem;
            font-weight: 800;
        }

        .logo-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .content-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border: 3px solid #00d2b8;
            backdrop-filter: blur(10px);
        }

        .page-title {
            color: #2d3748;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        th {
            background: linear-gradient(45deg, #00d2b8, #00a896);
            color: white;
            padding: 18px;
            text-align: left;
            font-weight: 600;
            font-size: 1.1rem;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }

        tr:hover {
            background-color: #f7fafc;
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        .delete-btn {
            background: linear-gradient(45deg, #e53e3e, #c53030);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
        }

        .statut-ouverte {
            color: #00d2b8;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .statut-en_cours {
            color: #ed8936;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .statut-resolue {
            color: #38a169;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .empty-message {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
            font-size: 1.2rem;
        }

        .empty-message i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px 10px;
            }
            
            .logo h1 {
                font-size: 2.2rem;
            }
            
            .content-box {
                padding: 20px;
            }
            
            table {
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 12px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <h1><i class="fas fa-leaf"></i> EcoTrack</h1>
            </div>
            <div class="logo-subtitle">Innovation Écologique</div>
        </div>

        <div class="content-box">
            <h2 class="page-title">
                <i class="fas fa-list"></i>
                Liste des Événements
            </h2>
            
            <?php if(empty($list)): ?>
                <div class="empty-message">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucun événement créé</h3>
                    <p>Commencez par créer votre premier événement écologique</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($list as $evenement): ?>
                        <tr>
                            <td><strong>#<?php echo $evenement['idEvenement'] ?></strong></td>
                            <td><?php echo htmlspecialchars($evenement['titre']) ?></td>
                            <td>
                                <?php 
                                $description = htmlspecialchars($evenement['description']);
                                echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                ?>
                            </td>
                            <td class="statut-<?php echo $evenement['statut'] ?>">
                                <?php 
                                $statutLabels = [
                                    'ouverte' => '🟢 Ouvert',
                                    'en_cours' => '🟡 En cours',
                                    'resolue' => '🔵 Terminé'
                                ];
                                echo $statutLabels[$evenement['statut']] ?? $evenement['statut'];
                                ?>
                            </td>
                            <td>
                                <a href="deleteEvenement.php?id=<?php echo $evenement['idEvenement'] ?>" class="delete-btn">
                                    <i class="fas fa-trash"></i>
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>