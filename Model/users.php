<?php 

class users {
    private ?int $id_users;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $role;
    private string $status;

    function __construct($id_users, $nom, $prenom, $email = 'haram.niangaly@esprit.tn', $role, $status) {
        $this->id_users = $id_users;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email; // email par défaut
        $this->role = $role;
        $this->status = $status;
    }

    // Getters
    function getIdUsers() { return $this->id_users; }
    function getNom() { return $this->nom; }
    function getPrenom() { return $this->prenom; }
    function getEmail() { return $this->email; }
    function getRole() { return $this->role; }
    function getStatus() { return $this->status; }

    // Setters
    function setIdUsers($id_users) { $this->id_users = $id_users; }
    function setNom($nom) { $this->nom = $nom; }
    function setPrenom($prenom) { $this->prenom = $prenom; }
    function setEmail($email) { $this->email = $email; }
    function setRole($role) { $this->role = $role; }
    function setStatus($status) { $this->status = $status; }

    // Saisir toutes les données
    function saisir($nom, $prenom, $email, $role, $status) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->role = $role;
        $this->status = $status;
    }

    // Afficher les informations
    function afficher() {
        echo "Nom: ".$this->nom."<br>".
             "Prenom: ".$this->prenom."<br>".
             "Email: ".$this->email."<br>".
             "Role: ".$this->role."<br>".
             "Status: ".$this->status."<br>";
    }
}

// Exemple d'utilisation
/*
$user = new Users(null, "Haram", "Niangaly", "haram.niangaly@esprit.tn", "patient", "actif");
$user->afficher();
*/

?>
