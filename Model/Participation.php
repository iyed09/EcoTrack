<?php

class Participation {
    private ?int $idParticipation;
    private int $idEvenement;
    private string $contenu;
    private string $dateParticipation;

    function __construct($idParticipation, $idEvenement, $contenu, $dateParticipation){
        $this->idParticipation = $idParticipation;
        $this->idEvenement = $idEvenement;
        $this->contenu = $contenu;
        $this->dateParticipation = $dateParticipation;
    }

    function getIdParticipation(){
        return $this->idParticipation;
    }
    function setIdParticipation($idParticipation){
        $this->idParticipation = $idParticipation;
    }
    function getIdEvenement(){
        return $this->idEvenement;
    }
    function setIdEvenement($idEvenement){
        $this->idEvenement = $idEvenement;
    }
    function getContenu(){
        return $this->contenu;
    }
    function getDateParticipation(){
        return $this->dateParticipation;
    }
    function setContenu($contenu){
        $this->contenu = $contenu;
    }
    function setDateParticipation($dateParticipation){
        $this->dateParticipation = $dateParticipation;
    }
    
    function saisir($idEvenement, $contenu, $dateParticipation){
        $this->idEvenement = $idEvenement;
        $this->contenu = $contenu;
        $this->dateParticipation = $dateParticipation;
    }

    function afficher(){
        echo "ID Participation: " . $this->idParticipation . "<br>" .
             "ID Événement: " . $this->idEvenement . "<br>" .
             "Contenu: " . $this->contenu . "<br>" .
             "Date: " . $this->dateParticipation . "<br>";
    }
}

// Exemple d'utilisation
// $participation = new Participation(null, 1, "Je souhaite participer à cet événement", "2023-12-01 10:00:00");
// $participation->afficher();
// $participation->saisir(2, "Nouveau contenu de participation", "2023-12-02 14:30:00");
// $participation->afficher();
?>