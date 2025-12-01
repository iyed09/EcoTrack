<?php

require_once __DIR__. '/../../Controller/ParticipationController.php';
require_once __DIR__. '/../../Model/Participation.php';

$participationC = new ParticipationController();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['idEvenement']) && isset($_POST['contenu']) && isset($_POST['dateParticipation'])){
        if(!empty($_POST['idEvenement']) && !empty($_POST['contenu']) && !empty($_POST['dateParticipation']))
        {
            $participation = new Participation(null, $_POST['idEvenement'], $_POST['contenu'], $_POST['dateParticipation']);
            $participationC->AddParticipation($participation);
        }
    }  
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Participation - EcoTrack</title>
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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 500px;
            border: 3px solid var(--eco-green);
            backdrop-filter: blur(10px);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            background: linear-gradient(45deg, var(--eco-green), var(--eco-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .logo p {
            color: #5a6779;
            font-size: 1rem;
            margin-top: 8px;
            font-weight: 500;
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #2d3748;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #2d3748;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        input, textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--eco-green);
            box-shadow: 0 0 0 3px rgba(0, 204, 136, 0.1);
            background: white;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, var(--eco-green), var(--eco-teal));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(0, 204, 136, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 204, 136, 0.4);
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 2rem;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="logo">
            <h1><i class="fas fa-leaf"></i> EcoTrack</h1>
            <p>Participer à un événement écologique</p>
        </div>
        
        <h2 class="form-title">
            <i class="fas fa-handshake"></i>
            Nouvelle Participation
        </h2>
        
        <form method="POST">
            <div class="form-group">
                <label>
                    <i class="fas fa-calendar-check"></i>
                    ID Événement
                </label>
                <input type="number" name="idEvenement" placeholder="Numéro de l'événement" required>
            </div>
            
            <div class="form-group">
                <label>
                    <i class="fas fa-comment-dots"></i>
                    Message de participation
                </label>
                <textarea name="contenu" placeholder="Exprimez votre intérêt, posez des questions, partagez vos idées..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>
                    <i class="fas fa-clock"></i>
                    Date de participation
                </label>
                <input type="datetime-local" name="dateParticipation" required>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i>
                Envoyer ma participation
            </button>
        </form>
    </div>
</body>
</html>