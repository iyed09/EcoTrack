<?php 
require __DIR__. "/../Model/config.php";
require __DIR__. "/../Model/user.php"; // Classe User avec getters et setters

class UserController {

    // Récupérer tous les utilisateurs
    function getAllUsers() {
        $sql = "SELECT * FROM users"; // table users
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    // Ajouter un utilisateur
    function addUser($user) {
        $sql = "INSERT INTO users(id_users, nom, prenom, email, role, status) VALUES
                (null, :nom, :prenom, :email, :role, :status)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue('nom', $user->getNom());
            $query->bindValue('prenom', $user->getPrenom());
            $query->bindValue('email', $user->getEmail()); // ex: 'haram.niangaly@esprit.tn'
            $query->bindValue('role', $user->getRole());
            $query->bindValue('status', $user->getStatus());
            $query->execute();
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    // Mettre à jour un utilisateur
    function updateUser($user, $id) {
        $sql = "UPDATE users 
                SET nom=:nom, prenom=:prenom, email=:email, role=:role, status=:status 
                WHERE id_users=:id_users";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        try {
            $query->execute([
                'id_users' => $id,
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'status' => $user->getStatus()
            ]);
            echo $query->rowCount();
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
}

// Exemple d'utilisation
/*
$user = new User('Haram', 'Niangaly', 'haram.niangaly@esprit.tn', 'patient', 'actif');
$userController = new UserController();
$userController->addUser($user);
print_r($userController->getAllUsers());
*/

?>
