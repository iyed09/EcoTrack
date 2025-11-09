<?php
require_once __DIR__ . "/../../Controller/usersController.php";

$userC = new usersController();

// Vérifier que l'ID est passé en GET
if (isset($_GET['id_users'])) {
    $userC->deleteUser($_GET['id_users']);
}

// Rediriger vers la liste des utilisateurs
header('Location: listUsers.php');
exit;
?>
