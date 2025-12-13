<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";
require_once "C:/xampppp/htdocs/ecotrack/Model/Produit.php";

class ProduitController {

    public static function addProduit($conn, $data) {
        $stmt = $conn->prepare("INSERT INTO produit (nom, categorie, empreinteCarbone) 
                                VALUES (:nom, :categorie, :empreinteCarbone)");
        return $stmt->execute([
            ':nom' => $data['nom'],
            ':categorie' => $data['categorie'],
            ':empreinteCarbone' => $data['empreinteCarbone']
        ]);
    }

    public static function editProduit($conn, $id, $data) {
        $stmt = $conn->prepare("UPDATE produit 
                                SET nom=:nom, categorie=:categorie, empreinteCarbone=:empreinteCarbone 
                                WHERE id=:id");
        return $stmt->execute([
            ':nom' => $data['nom'],
            ':categorie' => $data['categorie'],
            ':empreinteCarbone' => $data['empreinteCarbone'],
            ':id' => $id
        ]);
    }

    public static function removeProduit($conn, $id) {
        $stmt = $conn->prepare("DELETE FROM produit WHERE id=:id");
        return $stmt->execute([':id' => $id]);
    }

    public static function listProduit($conn) {
        try {
            $sql = "SELECT * FROM produit ORDER BY nom";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return [];
        }
    }

    public static function getProduitById($conn, $id) {
        try {
            $sql = "SELECT * FROM produit WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return false;
        }
    }

    public static function showProduit($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM produit WHERE id=:id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getProduitsByCategorie($conn, $categorie) {
        try {
            $sql = "SELECT * FROM produit WHERE categorie = ? ORDER BY nom";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$categorie]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return [];
        }
    }

    public static function getProduitsStats($conn) {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_produits,
                    AVG(empreinteCarbone) as moyenne_empreinte,
                    SUM(empreinteCarbone) as total_empreinte,
                    MIN(empreinteCarbone) as min_empreinte,
                    MAX(empreinteCarbone) as max_empreinte
                    FROM produit";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return false;
        }
    }

    public static function searchProduits($conn, $searchTerm) {
        try {
            $sql = "SELECT * FROM produit 
                    WHERE nom LIKE :search OR categorie LIKE :search 
                    ORDER BY nom";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':search' => '%' . $searchTerm . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
            return [];
        }
    }
}
?>