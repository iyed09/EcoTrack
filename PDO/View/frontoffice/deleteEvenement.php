<?php
require_once __DIR__."/../../Controller/EvenementController.php";

// Vérifier si l'ID est présent dans l'URL
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $evenementC = new EvenementController();
        
        // Supprimer l'événement avec l'identifiant
        $result = $evenementC->deleteEvenement($id);
        
        if($result) {
            echo "delete avec succes";
        } else {
            echo "Erreur: Impossible de supprimer l'événement";
        }
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage();
    }
} else {
    echo "Erreur: Aucun identifiant spécifié";
}
?>