<?php
require_once __DIR__."/../../Controller/ParticipationController.php";

// Activer le reporting d'erreurs pour débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si l'ID est présent
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        $participationC = new ParticipationController();
        
        // Tenter la suppression
        $result = $participationC->DeleteParticipation($id);
        
        if($result === true) {
            echo "delete avec succes";
        } else {
            echo "Erreur lors de la suppression";
        }
        
    } catch (Exception $e) {
        echo "Erreur technique: " . $e->getMessage();
    }
} else {
    echo "Aucun ID spécifié";
}
?>