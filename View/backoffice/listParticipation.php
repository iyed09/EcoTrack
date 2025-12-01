<?php 
require_once __DIR__.'/../../Controller/ParticipationController.php';
$participationC = new ParticipationController();
$list = $participationC->getAllParticipations();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Participations - EcoTrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --eco-green: #00cc88;
            --eco-teal: #00a896;
            --eco-cyan: #00d2b8;
            --eco-purple: #9b5de5;
            --dark-bg: #1a1a2e;
        }

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
            background: linear-gradient(45deg, var(--eco-green), var(--eco-teal));
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
            border: 3px solid var(--eco-green);
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
            background: linear-gradient(45deg, var(--eco-green), var(--eco-teal));
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
                <i class="fas fa-users"></i>
                Liste des Participations
            </h2>
            
            <?php if(empty($list)): ?>
                <div class="empty-message">
                    <i class="fas fa-users-slash"></i>
                    <h3>Aucune participation</h3>
                    <p>Les participants apparaîtront ici</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ID Événement</th>
                            <th>Contenu</th>
                            <th>Date Participation</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($list as $participation): ?>
                        <tr>
                            <td><strong>#<?php echo $participation['idParticipation'] ?></strong></td>
                            <td><?php echo $participation['idEvenement'] ?></td>
                            <td>
                                <?php 
                                $contenu = htmlspecialchars($participation['contenu']);
                                echo strlen($contenu) > 100 ? substr($contenu, 0, 100) . '...' : $contenu;
                                ?>
                            </td>
                            <td><?php echo $participation['dateParticipation'] ?></td>
                            <td>
                                <a href="deleteParticipation.php?id=<?php echo $participation['idParticipation'] ?>" class="delete-btn">
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