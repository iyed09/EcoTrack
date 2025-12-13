CREATE TABLE produit (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255),
    categorie VARCHAR(255),
    empreinteCarbone INT(11)
);
CREATE TABLE dechet (
    idDechet INT(11) AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(100),
    poids DECIMAL(8,2),
    recyclable TINYINT(1),
    idUser INT(100),
    FOREIGN KEY (id) REFERENCES produit()
);