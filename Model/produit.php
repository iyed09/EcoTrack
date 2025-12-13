<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";

class ProduitModel {
    private $id;  // Changé de idProduit à id
    private $nom;
    private $categorie;
    private $empreinteCarbone;
    private $conn;

    // Constructeur
    public function __construct($data = []) {
        $this->conn = Database::getConnexion();
        $this->id = $data['id'] ?? null;  // Changé de idProduit à id
        $this->nom = $data['nom'] ?? '';
        $this->categorie = $data['categorie'] ?? '';
        $this->empreinteCarbone = $data['empreinteCarbone'] ?? 0;
    }

    // Getters
    public function getId() { return $this->id; }  // Changé de getIdProduit à getId
    public function getNom() { return $this->nom; }
    public function getCategorie() { return $this->categorie; }
    public function getEmpreinteCarbone() { return $this->empreinteCarbone; }

    // Setters
    public function setNom($nom) { $this->nom = $nom; }
    public function setCategorie($categorie) { $this->categorie = $categorie; }
    public function setEmpreinteCarbone($empreinteCarbone) { $this->empreinteCarbone = $empreinteCarbone; }
}
?>