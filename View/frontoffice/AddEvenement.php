<?php
require_once __DIR__. '/../../Controller/EvenementController.php';

$evenementC = new EvenementController();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['titre']) && isset($_POST['description']) && isset($_POST['statut'])){
        if(!empty($_POST['titre']) && !empty($_POST['description']) && !empty($_POST['statut'])){
            $evenement = new Evenement(null, $_POST['titre'], $_POST['description'], $_POST['statut']);
            $evenementC->AddEvenement($evenement);
            
            header('Location: listEvenement.php');
            exit();
        }
    }  
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Événement - EcoTrack</title>
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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 500px;
            border: 3px solid #00d2b8;
            backdrop-filter: blur(10px);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            background: linear-gradient(45deg, #00d2b8, #00a896);
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

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #2d3748;
            font-weight: 600;
            font-size: 1.1rem;
        }

        input, textarea, select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #00d2b8;
            box-shadow: 0 0 0 3px rgba(0, 210, 184, 0.1);
            background: white;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #00d2b8, #00a896);
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
            box-shadow: 0 4px 15px rgba(0, 210, 184, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 210, 184, 0.4);
        }
    </style>
</head>
<body>
    <div class="form-box">
        <div class="logo">
            <h1><i class="fas fa-leaf"></i> EcoTrack</h1>
            <p>Créer un nouvel événement écologique</p>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="titre" placeholder="Titre de l'événement" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Description de l'événement..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Statut</label>
                <select name="statut" required>
                    <option value="">Choisir un statut</option>
                    <option value="ouverte">Ouvert</option>
                    <option value="en_cours">En cours</option>
                    <option value="resolue">Terminé</option>
                </select>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Envoyer
            </button>
        </form>
    </div>
</body>
</html>