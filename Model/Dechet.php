<?php
require_once "C:/xampppp/htdocs/ecotrack/Database.php";

class DechetModel {
    private $idDechet;
    private $type;
    private $poids;
    private $recyclable;
    private $id; // clé étrangère vers produit
    private $conn;

    public function __construct($data = []) {
        $this->conn = Database::getConnexion();
        $this->idDechet = $data['idDechet'] ?? null;
        $this->type = $data['type'] ?? '';
        $this->poids = $data['poids'] ?? 0.00;
        $this->recyclable = $data['recyclable'] ?? 0;
        $this->id = $data['id'] ?? null;
    }

    // Getters
    public function getIdDechet() { return $this->idDechet; }
    public function getType() { return $this->type; }
    public function getPoids() { return $this->poids; }
    public function getRecyclable() { return $this->recyclable; }
    public function getId() { return $this->id; }

    // Setters
    public function setType($type) { $this->type = $type; }
    public function setPoids($poids) { $this->poids = $poids; }
    public function setRecyclable($recyclable) { $this->recyclable = $recyclable; }
    public function setId($id) { $this->id = $id; }
}
?>