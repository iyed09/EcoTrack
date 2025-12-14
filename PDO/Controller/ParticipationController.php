<?php 
require __DIR__. "/../Model/config.php";
require __DIR__. "/../Model/Participation.php";

class ParticipationController{
    function getAllParticipations(){
        $sql="SELECT * FROM participation";
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

    function AddParticipation($participation){
        $sql="INSERT INTO participation(idEvenement, contenu, dateParticipation) VALUES
        (:idEvenement, :contenu, :dateParticipation)";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->bindValue('idEvenement', $participation->getIdEvenement());
            $query->bindValue('contenu', $participation->getContenu());
            $query->bindValue('dateParticipation', $participation->getDateParticipation());
            $query->execute();
            return true;
        }
        catch(Exception $e){
            echo ("erreur".$e->getMessage());
            return false;
        }
    }

    function updateParticipation($participation, $id){
        $sql="UPDATE participation SET idEvenement=:idEvenement, contenu=:contenu, dateParticipation=:dateParticipation WHERE idParticipation=:id";
        $db=config::getConnexion();
        $query=$db->prepare($sql);
        try{
            $query->execute([
                'id'=>$id,
                'idEvenement'=>$participation->getIdEvenement(),
                'contenu'=>$participation->getContenu(),
                'dateParticipation'=>$participation->getDateParticipation()
            ]);
            return $query->rowCount();
        }
        catch(Exception $e){
            echo ("erreur".$e->getMessage());
            return false;
        }
    }

    function DeleteParticipation($id){
        $sql="DELETE FROM participation WHERE idParticipation=:id";
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

    function getParticipationsByEvenement($idEvenement){
        $sql="SELECT * FROM participation WHERE idEvenement=:idEvenement";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->execute(['idEvenement'=>$idEvenement]);
            return $query->fetchAll();
        }
        catch(Exception $e){
            echo ("erreur".$e->getMessage());
            return [];
        }
    }
}

// Exemple d'utilisation
// $participation = new Participation(null, 1, "Ceci est une participation", "2023-12-01 10:00:00");
// AddParticipation($participation);
// updateParticipation($participation, 1);
// DeleteParticipation(1);
// print_r(getAllParticipations());
?>