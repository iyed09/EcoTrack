-- 1) Création de la base
CREATE DATABASE IF NOT EXISTS ecotrack
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ecotrack;

-- 2) Table des administrateurs
DROP TABLE IF EXISTS admin_users;
CREATE TABLE admin_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin par défaut (login: admin / mot de passe: 12345678)
INSERT INTO admin_users (username, password)
VALUES ('admin', '12345678');

-- 3) Table des sources d'énergie
DROP TABLE IF EXISTS source_energie;
CREATE TABLE source_energie (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(150) NOT NULL,
  type VARCHAR(50) NOT NULL,             -- ex: Renouvelable / Non-Renouvelable
  cout_moyen DECIMAL(10,4) NOT NULL,     -- €/kWh
  emission_carbone DECIMAL(10,4) NOT NULL, -- kgCO2/kWh
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Quelques sources par défaut (optionnel)
INSERT INTO source_energie (nom, type, cout_moyen, emission_carbone, description) VALUES
('Solaire Photovoltaïque', 'Renouvelable', 0.10, 0.05, 'Panneaux solaires haute efficacité'),
('Éolien Offshore',        'Renouvelable', 0.08, 0.03, 'Parc éolien en mer'),
('Gaz Naturel',            'Non-Renouvelable', 0.15, 0.45, 'Centrale à cycle combiné'),
('Nucléaire',              'Non-Renouvelable', 0.09, 0.01, 'Centrale nucléaire'),
('Biomasse',               'Renouvelable', 0.11, 0.18, 'Valorisation des déchets organiques');

-- 4) Table des consommations (frontoffice/backoffice)
DROP TABLE IF EXISTS energie;
CREATE TABLE energie (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_energie_id INT UNSIGNED NOT NULL,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  consommation DECIMAL(12,2) NOT NULL,   -- kWh
  cout_total DECIMAL(12,2) NOT NULL,     -- €
  emission_totale DECIMAL(12,2) NOT NULL, -- kgCO2
  utilisateur VARCHAR(100) DEFAULT 'Utilisateur',
  statut VARCHAR(20) DEFAULT 'En attente', -- En attente / Validé / Rejeté
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_energie_source
    FOREIGN KEY (source_energie_id)
    REFERENCES source_energie(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) (Optionnel) Vue rapide pour stats globales
-- Ton code PHP calcule déjà les stats par SUM, donc ceci est facultatif.
-- SELECT COUNT(*) , SUM(consommation), SUM(cout_total), SUM(emission_totale) FROM energie;
