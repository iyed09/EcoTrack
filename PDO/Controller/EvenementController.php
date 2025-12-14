<?php 
require __DIR__. "/../Model/config.php";
require __DIR__. "/../Model/Evenement.php";

class EvenementController{
    function getAllEvenements(){
        $sql="SELECT * FROM evenement";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        }
        catch(Exception $e){
            echo ("erreur".$e->getMessage());
        }
    }

    function addEvenement($evenement){
        $sql="INSERT INTO evenement(titre, description, statut) VALUES (:titre, :description, :statut)";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->bindValue('titre',$evenement->getTitre());
            $query->bindValue('description',$evenement->getDescription());
            $query->bindValue('statut',$evenement->getStatut());
            $query->execute();
            return true;
        }
        catch(Exception $e){
            echo ("erreur".$e->getMessage());
            return false;
        }
    }

    function updateEvenement($evenement, $id){
        $sql="UPDATE evenement SET titre=:titre, description=:description, statut=:statut WHERE idEvenement=:id";
        $db=config::getConnexion();
        $query=$db->prepare($sql);
        try{
            $query->execute([
                'id'=>$id,
                'titre'=>$evenement->getTitre(),
                'description'=>$evenement->getDescription(),
                'statut'=>$evenement->getStatut()
            ]);
            echo $query->rowCount();
            return $query->rowCount();
        }
        catch(Exception $e){
            echo ("erreur".$e->getMessage());
            return false;
        }
    }

    function deleteEvenement($id){
        $sql="DELETE FROM evenement WHERE idEvenement=:id";
        $db=config::getConnexion();
        $query=$db->prepare($sql);
        try{
            $query->execute(['id'=>$id]);
            return true;
        }
        catch(Exception $e){
            echo ("erreur".$e->getMessage());
            return false;
        }
    }
    
    // Fonction pour récupérer un événement par son ID (optionnel, comme dans PersonneController)
    function getEvenementById($id){
        $sql="SELECT * FROM evenement WHERE idEvenement=:id";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->execute(['id'=>$id]);
            return $query->fetch();
        }
        catch(Exception $e){
            echo ("erreur".$e->getMessage());
            return null;
        }
    }
}

// Exemples d'utilisation (à décommenter si nécessaire)
// $evenement = new Evenement("Titre événement", "Description de l'événement", "actif");
// $controller = new EvenementController();
// $controller->addEvenement($evenement);
// $controller->updateEvenement($evenement, 1);
// $controller->deleteEvenement(2);
// print_r($controller->getAllEvenements());
?>