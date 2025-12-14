<?php

class Evenement {
    private ?int $idEvenement;
    private string $titre;
    private string $description;
    private string $statut;

    // 🔹 Constructeur
    public function __construct($idEvenement = null, $titre, $description, $statut = 'ouverte') {
        $this->idEvenement = $idEvenement;
        $this->titre = $titre;
        $this->description = $description;
        $this->statut = $statut;
    }

    // 🔹 Getters
    public function getIdEvenement() { 
        return $this->idEvenement; 
    }
    
    public function getTitre() { 
        return $this->titre; 
    }
    
    public function getDescription() { 
        return $this->description; 
    }
    
    public function getStatut() { 
        return $this->statut; 
    }

    // 🔹 Setters
    public function setIdEvenement($idEvenement) { 
        $this->idEvenement = $idEvenement; 
    }
    
    public function setTitre($titre) { 
        $this->titre = $titre; 
    }
    
    public function setDescription($description) { 
        $this->description = $description; 
    }
    
    public function setStatut($statut) { 
        $this->statut = $statut; 
    }

    // 🔹 Méthode pour saisir les infos
    public function saisir($titre, $description, $statut) {
        $this->titre = $titre;
        $this->description = $description;
        $this->statut = $statut;
    }

    // 🔹 Méthode pour afficher les infos
    public function afficher() {
        echo "📋 Événement :<br>";
        echo "ID : " . $this->idEvenement . "<br>";
        echo "Titre : " . $this->titre . "<br>";
        echo "Description : " . $this->description . "<br>";
        echo "Statut : " . $this->statut . "<br>";
    }
}

// ✅ Exemple d'utilisation
/*
$event = new Evenement(null, "Nettoyage de la plage", "Organisation d'une journée de nettoyage de la plage communautaire", "ouverte");
$event->afficher();
*/
?>