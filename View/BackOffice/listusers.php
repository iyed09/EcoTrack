<?php 
require_once __DIR__ . '/../../Controller/usersController.php';

$userC = new usersController();
$list = $userC->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Utilisateurs</title>
</head>
<body>
    <h2>Liste des Utilisateurs</h2>
    <table border="1">
        <tr>
            <th>Id</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach($list as $user){ ?>
        <tr>
            <td><?php echo $user['id_users']; ?></td>
            <td><?php echo $user['nom']; ?></td>
            <td><?php echo $user['prenom']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo $user['role']; ?></td>
            <td><?php echo $user['status']; ?></td>
            <td>
                <a href="deleteUser.php?id_users=<?php echo $user['id_users']; ?>">Supprimer</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
