<?php

require_once __DIR__ . '/../../Controller/usersController.php';

$userC = new usersController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['nom']) && isset($_POST['prenom']) &&
        isset($_POST['email']) && isset($_POST['role']) && isset($_POST['status'])
    ) {
        if (
            !empty($_POST['nom']) && !empty($_POST['prenom']) &&
            !empty($_POST['email']) && !empty($_POST['role']) && !empty($_POST['status'])
        ) {
            $user = new Users(
                null,
                $_POST['nom'],
                $_POST['prenom'],
                $_POST['email'],
                $_POST['role'],
                $_POST['status']
            );
            $userC->addUser($user);
            echo "Utilisateur ajouté avec succès !";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Utilisateur</title>
</head>
<body>
    <form method="POST">
        <label>Nom</label>
        <input type="text" name="nom" required><br>

        <label>Prénom</label>
        <input type="text" name="prenom" required><br>

        <label>Email</label>
        <input type="email" name="email" required><br>

        <label>Rôle</label>
        <select name="role" required>
            <option value="patient">Patient</option>
            <option value="medecin">Médecin</option>
            <option value="admin">Admin</option>
        </select><br>

        <label>Status</label>
        <select name="status" required>
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
        </select><br>

        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
