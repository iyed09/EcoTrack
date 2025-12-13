<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Model/Dechet.php";

class DechetController {

    public static function addDechet($conn, $data) {
        $stmt = $conn->prepare("INSERT INTO dechet (type, poids, recyclable, id) 
                                VALUES (:type, :poids, :recyclable, :id)");
        return $stmt->execute([
            ':type' => $data['type'],
            ':poids' => $data['poids'],
            ':recyclable' => $data['recyclable'],
            ':id' => $data['id']
        ]);
    }

    public static function editDechet($conn, $idDechet, $data) {
        $stmt = $conn->prepare("UPDATE dechet 
                                SET type=:type, poids=:poids, recyclable=:recyclable, id=:id 
                                WHERE idDechet=:idDechet");
        return $stmt->execute([
            ':type' => $data['type'],
            ':poids' => $data['poids'],
            ':recyclable' => $data['recyclable'],
            ':id' => $data['id'],
            ':idDechet' => $idDechet
        ]);
    }

    public static function removeDechet($conn, $idDechet) {
        try {
            $stmt = $conn->prepare("DELETE FROM dechet WHERE idDechet=:idDechet");
            return $stmt->execute([':idDechet' => $idDechet]);
        } catch (PDOException $e) {
            // Gérer les erreurs de contrainte de clé étrangère si nécessaire
            if ($e->getCode() == "23000") {
                return "FK_CONSTRAINT";
            }
            return false;
        }
    }

    public static function listDechet($conn) {
        try {
            // Jointure avec la table produit pour récupérer le nom du produit
            $sql = "SELECT d.*, p.nom as produit_nom 
                    FROM dechet d 
                    LEFT JOIN produit p ON d.id = p.id 
                    ORDER BY d.idDechet";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return [];
        }
    }

    public static function getDechetById($conn, $idDechet) {
        try {
            // Jointure avec la table produit
            $sql = "SELECT d.*, p.nom as produit_nom 
                    FROM dechet d 
                    LEFT JOIN produit p ON d.id = p.id 
                    WHERE d.idDechet = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$idDechet]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return false;
        }
    }

    // Autres méthodes si nécessaire
}
?>