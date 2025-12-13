-- Database Creation
CREATE DATABASE IF NOT EXISTS ecotrack;
USE ecotrack;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    total_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Energy Sources Table
CREATE TABLE IF NOT EXISTS energy_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    emission_factor DECIMAL(10,4) NOT NULL,
    unit VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Energy Consumption Table
CREATE TABLE IF NOT EXISTS energy_consumption (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    source_id INT,
    amount DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (source_id) REFERENCES energy_sources(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transport Types Table
CREATE TABLE IF NOT EXISTS transport_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    emission_per_km DECIMAL(10,4) NOT NULL,
    icon VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transport Entries Table
CREATE TABLE IF NOT EXISTS transport_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    transport_id INT,
    distance_km DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (transport_id) REFERENCES transport_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Categories Table
CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    eco_impact_score DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Purchases Table
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category_id INT,
    product_name VARCHAR(200) NOT NULL,
    quantity INT DEFAULT 1,
    is_eco_friendly BOOLEAN DEFAULT FALSE,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waste Types Table
CREATE TABLE IF NOT EXISTS waste_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    recyclable BOOLEAN DEFAULT FALSE,
    impact_score DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waste Entries Table
CREATE TABLE IF NOT EXISTS waste_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    waste_type_id INT,
    weight_kg DECIMAL(10,2) NOT NULL,
    properly_disposed BOOLEAN DEFAULT TRUE,
    date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (waste_type_id) REFERENCES waste_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trash Reports Table
CREATE TABLE IF NOT EXISTS trash_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT,
    location_description TEXT NOT NULL,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    description TEXT NOT NULL,
    photo_path VARCHAR(255),
    status VARCHAR(20) DEFAULT 'pending',
    admin_response TEXT,
    response_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Content Table
CREATE TABLE IF NOT EXISTS admin_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Data: Energy Sources
INSERT INTO energy_sources (name, emission_factor, unit) VALUES
('Electricity', 0.4, 'kWh'),
('Natural Gas', 2.0, 'm³'),
('Heating Oil', 2.5, 'L'),
('Solar Panel', 0.05, 'kWh'),
('Wind Energy', 0.02, 'kWh');

-- Seed Data: Transport Types
INSERT INTO transport_types (name, emission_per_km, icon) VALUES
('Car (Gasoline)', 0.21, 'bi-car-front'),
('Car (Diesel)', 0.17, 'bi-car-front'),
('Electric Car', 0.05, 'bi-ev-front'),
('Bus', 0.09, 'bi-bus-front'),
('Train', 0.04, 'bi-train-front'),
('Bicycle', 0.0, 'bi-bicycle'),
('Walking', 0.0, 'bi-person-walking'),
('Motorcycle', 0.10, 'bi-scooter'),
('Airplane', 0.25, 'bi-airplane');

-- Seed Data: Product Categories
INSERT INTO product_categories (name, eco_impact_score) VALUES
('Electronics', 8.5),
('Clothing', 5.0),
('Food - Local', 2.0),
('Food - Imported', 6.0),
('Household Items', 4.0),
('Personal Care', 3.5),
('Packaging', 7.0);

-- Seed Data: Waste Types
INSERT INTO waste_types (name, recyclable, impact_score) VALUES
('Plastic', TRUE, 8.0),
('Paper', TRUE, 3.0),
('Glass', TRUE, 4.0),
('Metal', TRUE, 5.0),
('Organic', TRUE, 2.0),
('Electronic Waste', TRUE, 9.0),
('General Waste', FALSE, 7.0);

-- Points History Table
CREATE TABLE IF NOT EXISTS points_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    points INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_description TEXT,
    reference_id INT,
    reference_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Achievements Table
CREATE TABLE IF NOT EXISTS achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    points_required INT DEFAULT 0,
    action_type VARCHAR(50),
    action_count INT DEFAULT 0,
    badge_icon VARCHAR(50),
    badge_color VARCHAR(20) DEFAULT 'primary',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Achievements Table
CREATE TABLE IF NOT EXISTS user_achievements (
    user_id INT,
    achievement_id INT,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, achievement_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Data: Achievements
INSERT INTO achievements (name, description, points_required, action_type, action_count, badge_icon) VALUES
('Eco Starter', 'Earn your first 100 points', 100, NULL, 0, 'bi-star'),
('Waste Warrior', 'Log 10 waste entries', 0, 'waste_entry', 10, 'bi-trash'),
('Energy Saver', 'Log 5 energy saving activities', 0, 'energy_entry', 5, 'bi-lightning'),
('Green Traveler', 'Log 50km of eco-friendly transport', 0, 'transport_entry', 10, 'bi-bicycle'),
('Community Hero', 'Report 5 trash locations', 0, 'trash_report', 5, 'bi-geo-alt');

-- Admin User (Password: admin123)
-- Note: The password hash here is generated using PHP's password_hash(). 
-- If you need to reset it, you can use an online bcrypt generator or run the PHP setup script.
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@ecotrack.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'admin');

-- Password Resets Table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generated Dummy Data

INSERT INTO users (id, name, email, password, role) VALUES (2, 'Bob Rodriguez', 'bob.rodriguez2@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (3, 'Diana Rodriguez', 'diana.rodriguez3@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (4, 'Hank Garcia', 'hank.garcia4@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (5, 'Grace Garcia', 'grace.garcia5@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (6, 'John Miller', 'john.miller6@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (7, 'Hank Garcia', 'hank.garcia7@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (8, 'Alice Jones', 'alice.jones8@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (9, 'Jane Davis', 'jane.davis9@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (10, 'Eve Doe', 'eve.doe10@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (11, 'Bob Rodriguez', 'bob.rodriguez11@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (12, 'Diana Williams', 'diana.williams12@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (13, 'Alice Miller', 'alice.miller13@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (14, 'Jane Smith', 'jane.smith14@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (15, 'Diana Johnson', 'diana.johnson15@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (16, 'Bob Jones', 'bob.jones16@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (17, 'Hank Doe', 'hank.doe17@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (18, 'Jane Brown', 'jane.brown18@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (19, 'Hank Williams', 'hank.williams19@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (20, 'Eve Brown', 'eve.brown20@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (21, 'Alice Johnson', 'alice.johnson21@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (22, 'Diana Miller', 'diana.miller22@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (23, 'Charlie Miller', 'charlie.miller23@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (24, 'John Jones', 'john.jones24@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (25, 'Hank Jones', 'hank.jones25@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (26, 'John Rodriguez', 'john.rodriguez26@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (27, 'Hank Johnson', 'hank.johnson27@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (28, 'Jane Rodriguez', 'jane.rodriguez28@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (29, 'Frank Smith', 'frank.smith29@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (30, 'Alice Brown', 'alice.brown30@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (31, 'Frank Davis', 'frank.davis31@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (32, 'Grace Garcia', 'grace.garcia32@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (33, 'John Rodriguez', 'john.rodriguez33@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (34, 'Hank Smith', 'hank.smith34@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (35, 'Eve Williams', 'eve.williams35@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (36, 'Diana Garcia', 'diana.garcia36@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (37, 'Alice Davis', 'alice.davis37@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (38, 'John Davis', 'john.davis38@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (39, 'Bob Davis', 'bob.davis39@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (40, 'Jane Jones', 'jane.jones40@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (41, 'Hank Garcia', 'hank.garcia41@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (42, 'Charlie Jones', 'charlie.jones42@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (43, 'Diana Jones', 'diana.jones43@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (44, 'John Jones', 'john.jones44@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (45, 'Hank Davis', 'hank.davis45@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (46, 'Charlie Davis', 'charlie.davis46@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (47, 'Charlie Rodriguez', 'charlie.rodriguez47@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (48, 'John Johnson', 'john.johnson48@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (49, 'Bob Garcia', 'bob.garcia49@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (50, 'John Williams', 'john.williams50@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO users (id, name, email, password, role) VALUES (51, 'Alice Smith', 'alice.smith51@example.com', '$2y$10$byuE1pZEi8g5p./wlNaSYO.SfBq6ZFY0J3xklLez9nXn0I9/LoNpK', 'user');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (45, 3, 282.71, '2025-05-30');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (23, 4, 79.38, '2025-09-26');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (29, 1, 117.43, '2025-09-15');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (33, 4, 483.71, '2025-06-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (33, 2, 270.81, '2025-11-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (38, 2, 198.94, '2025-10-30');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (27, 5, 356.65, '2025-06-20');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (40, 3, 258.59, '2025-02-26');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (41, 3, 394.75, '2025-10-18');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (27, 2, 145.66, '2025-09-22');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (39, 5, 455.05, '2025-03-12');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (40, 1, 153.99, '2025-03-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (34, 4, 422.38, '2025-02-28');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (50, 2, 388.91, '2025-11-14');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (43, 1, 38.59, '2025-05-14');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (15, 1, 467.48, '2025-09-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (10, 2, 311.93, '2025-06-15');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (25, 5, 76.55, '2025-06-10');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (3, 3, 344.6, '2024-12-31');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (24, 1, 330.13, '2025-11-14');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (40, 2, 460.35, '2025-08-17');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (49, 4, 52.25, '2025-02-13');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (16, 4, 155.58, '2025-09-09');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (33, 3, 161.11, '2025-05-27');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (3, 5, 144.58, '2025-03-09');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (9, 3, 411.61, '2025-10-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (15, 2, 423.9, '2025-10-29');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (18, 5, 428.48, '2025-06-05');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (45, 1, 109.16, '2025-12-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (12, 1, 224.65, '2025-02-15');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (28, 5, 298.54, '2025-09-08');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (49, 2, 494.64, '2025-06-07');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (25, 3, 117.31, '2025-06-07');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (45, 3, 495.59, '2025-11-25');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (37, 5, 195.61, '2025-11-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (36, 3, 254.55, '2025-10-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (28, 2, 203.29, '2025-02-05');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (49, 3, 102.48, '2025-09-25');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (41, 5, 408.01, '2025-08-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (20, 1, 175.78, '2025-04-08');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (9, 3, 93.29, '2025-09-19');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (2, 2, 63.93, '2025-11-30');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (5, 2, 235.74, '2025-01-06');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (32, 3, 447.18, '2025-05-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (31, 1, 99.46, '2025-09-15');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (16, 2, 425.05, '2025-09-25');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (47, 2, 278.27, '2025-09-25');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (50, 3, 280.99, '2025-11-28');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (44, 5, 280.18, '2025-05-13');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (18, 1, 472.36, '2025-04-28');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (22, 2, 448.13, '2025-06-09');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (36, 4, 120.59, '2025-07-31');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (16, 4, 392.13, '2025-05-20');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (33, 3, 363.8, '2025-04-09');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (20, 4, 383.93, '2025-05-25');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (50, 1, 46.5, '2025-10-14');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (31, 4, 194.19, '2025-11-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (14, 4, 228.3, '2025-10-19');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (3, 2, 340.0, '2024-12-19');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (19, 5, 311.54, '2025-11-06');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (41, 2, 260.1, '2025-11-12');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (19, 2, 363.26, '2025-09-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (37, 2, 71.97, '2025-03-29');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (8, 3, 470.28, '2025-09-12');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (18, 5, 369.8, '2024-12-28');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (31, 2, 223.81, '2025-06-03');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (40, 4, 434.44, '2025-01-05');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (7, 3, 239.64, '2025-02-28');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (28, 3, 52.12, '2025-02-28');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (39, 2, 237.45, '2025-03-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (34, 5, 170.56, '2025-10-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (27, 3, 387.28, '2025-06-17');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (25, 5, 375.59, '2025-12-06');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (13, 3, 322.95, '2025-06-30');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (31, 4, 16.9, '2025-04-07');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (15, 5, 345.56, '2025-08-14');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (51, 3, 154.84, '2024-12-18');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (27, 2, 45.52, '2025-08-06');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (19, 2, 19.8, '2025-03-22');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (10, 1, 60.76, '2025-06-11');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (2, 5, 283.66, '2025-02-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (27, 2, 109.77, '2025-07-15');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (15, 2, 452.97, '2025-04-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (39, 4, 238.61, '2025-03-18');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (51, 2, 438.1, '2025-07-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (37, 4, 222.18, '2025-03-30');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (45, 4, 149.22, '2025-11-20');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (45, 1, 286.32, '2025-05-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (33, 2, 174.08, '2025-02-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (30, 5, 423.87, '2025-06-12');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (49, 1, 269.76, '2025-11-30');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (43, 1, 94.16, '2025-04-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (11, 1, 433.07, '2025-01-29');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (16, 3, 129.9, '2025-10-21');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (45, 5, 266.98, '2025-07-27');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (47, 4, 374.07, '2025-06-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (51, 5, 120.3, '2025-04-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (48, 3, 448.36, '2025-03-12');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (37, 3, 136.34, '2025-01-14');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (4, 2, 157.93, '2025-08-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (13, 4, 129.58, '2025-09-21');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (43, 5, 161.39, '2025-06-05');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (46, 2, 58.8, '2025-02-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (41, 4, 365.67, '2025-06-11');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (32, 3, 292.61, '2025-09-19');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (11, 4, 346.69, '2025-06-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (30, 4, 423.34, '2025-11-24');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (24, 1, 62.79, '2025-03-23');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (3, 3, 387.26, '2025-02-22');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (19, 4, 297.67, '2025-01-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (11, 5, 113.71, '2025-01-19');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (28, 4, 71.98, '2025-08-24');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (29, 5, 283.2, '2025-10-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (50, 3, 233.42, '2025-10-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (39, 3, 250.77, '2025-01-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (18, 3, 243.64, '2025-11-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (45, 4, 145.9, '2025-04-09');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (12, 4, 231.25, '2025-04-25');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (39, 5, 329.04, '2025-07-29');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (2, 4, 92.37, '2025-02-09');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (4, 4, 99.33, '2025-06-05');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (35, 4, 131.86, '2024-12-29');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (47, 1, 79.19, '2025-09-14');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (3, 4, 342.75, '2025-05-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (33, 3, 11.52, '2025-06-21');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (19, 4, 355.38, '2025-03-29');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (22, 5, 378.56, '2025-06-26');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (35, 1, 165.99, '2025-01-24');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (32, 2, 76.02, '2025-06-11');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (38, 5, 89.74, '2025-08-20');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (5, 5, 383.97, '2025-11-11');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (39, 4, 274.45, '2025-06-29');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (28, 5, 57.0, '2025-07-03');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (50, 4, 47.96, '2025-05-05');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (9, 5, 53.71, '2025-07-24');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (13, 2, 393.69, '2025-02-14');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (27, 2, 247.23, '2025-11-09');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (31, 3, 305.18, '2025-09-10');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (7, 2, 182.24, '2025-02-05');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (32, 1, 489.61, '2025-07-12');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (14, 2, 170.89, '2025-01-19');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (9, 4, 138.95, '2025-11-11');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (34, 5, 94.11, '2024-12-08');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (49, 3, 12.56, '2025-01-31');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (19, 5, 317.53, '2025-10-12');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (2, 5, 281.65, '2025-09-25');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (8, 4, 161.24, '2025-03-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (44, 5, 27.24, '2025-09-12');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (11, 1, 496.51, '2025-10-09');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (7, 2, 218.48, '2024-12-27');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (8, 2, 409.09, '2025-07-17');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (8, 2, 50.5, '2025-11-12');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (27, 2, 245.45, '2025-06-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (13, 4, 306.74, '2025-04-06');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (18, 5, 113.06, '2025-06-26');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (32, 3, 371.75, '2025-08-25');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (36, 2, 230.08, '2024-12-31');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (41, 2, 71.24, '2025-04-03');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (13, 5, 172.52, '2025-02-11');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (25, 1, 17.56, '2025-10-30');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (40, 5, 43.98, '2025-04-30');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (2, 3, 147.68, '2025-03-03');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (5, 1, 233.01, '2025-07-22');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (34, 4, 386.05, '2025-11-03');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (4, 2, 139.74, '2025-11-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (33, 2, 76.8, '2025-10-05');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (13, 3, 41.93, '2025-03-19');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (17, 1, 414.12, '2025-09-04');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (47, 2, 334.47, '2025-08-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (12, 2, 298.93, '2025-08-08');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (37, 1, 373.0, '2025-10-24');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (39, 5, 249.13, '2025-01-30');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (39, 5, 400.23, '2025-08-06');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (22, 3, 437.1, '2025-10-11');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (27, 1, 421.82, '2025-03-14');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (40, 3, 271.92, '2025-09-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (45, 5, 474.64, '2025-03-21');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (34, 4, 400.06, '2025-02-25');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (38, 4, 451.64, '2025-09-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (35, 3, 69.38, '2025-03-15');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (18, 5, 340.31, '2025-01-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (19, 4, 488.21, '2024-12-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (35, 3, 195.2, '2025-05-19');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (35, 4, 412.12, '2025-11-06');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (44, 5, 170.22, '2025-08-27');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (41, 2, 327.31, '2025-08-15');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (12, 3, 231.3, '2025-01-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (14, 5, 166.58, '2025-02-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (27, 3, 325.72, '2024-12-20');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (33, 3, 466.75, '2025-06-01');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (43, 2, 307.23, '2025-08-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (48, 5, 473.27, '2025-05-02');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (45, 3, 180.75, '2025-01-16');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (26, 4, 307.84, '2025-04-21');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (2, 5, 171.33, '2025-03-13');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (25, 4, 80.36, '2025-05-18');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (48, 4, 206.36, '2025-04-26');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (30, 5, 244.14, '2025-08-10');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (16, 2, 466.82, '2025-06-29');
INSERT INTO energy_consumption (user_id, source_id, amount, date) VALUES (21, 5, 432.73, '2025-02-08');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (50, 4, 84.31, '2025-10-25');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (40, 1, 71.29, '2025-04-02');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (31, 3, 92.4, '2025-12-06');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (36, 1, 39.16, '2025-07-29');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (3, 9, 87.07, '2025-10-23');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (32, 4, 84.59, '2025-05-06');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (41, 5, 23.25, '2025-05-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (22, 5, 88.5, '2025-03-05');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 6, 75.85, '2025-01-15');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 2, 44.08, '2025-07-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (25, 9, 74.51, '2025-11-26');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (24, 1, 23.53, '2025-11-03');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (44, 4, 80.69, '2025-05-06');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (19, 7, 7.03, '2025-06-30');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (36, 9, 70.61, '2025-09-09');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (47, 3, 43.55, '2025-05-16');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (3, 1, 42.34, '2025-08-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (28, 9, 26.36, '2025-07-01');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (5, 1, 59.06, '2025-11-27');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (7, 8, 99.81, '2025-12-03');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (26, 6, 45.29, '2025-09-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (2, 7, 64.08, '2025-04-12');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (3, 7, 51.08, '2025-01-14');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (18, 3, 37.82, '2025-01-29');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (33, 1, 21.1, '2025-03-13');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 9, 59.69, '2025-05-28');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (24, 7, 39.46, '2025-04-16');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (2, 7, 28.67, '2025-05-11');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (39, 1, 62.49, '2024-12-22');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (34, 6, 84.53, '2025-07-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (43, 6, 61.64, '2025-10-31');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (35, 8, 81.27, '2025-10-03');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (26, 8, 79.04, '2025-08-16');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (12, 2, 84.12, '2025-05-18');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (34, 1, 43.52, '2025-04-26');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (51, 4, 70.74, '2024-12-24');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (23, 1, 58.56, '2025-07-25');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 2, 34.82, '2024-12-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (10, 8, 52.32, '2025-08-15');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (10, 2, 65.12, '2025-09-26');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (21, 4, 85.86, '2025-11-01');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (33, 4, 67.76, '2025-06-24');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (51, 7, 38.02, '2025-10-22');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (25, 6, 82.37, '2025-01-12');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (10, 2, 61.47, '2025-04-02');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (35, 4, 49.72, '2025-03-19');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (2, 6, 99.65, '2025-11-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (22, 4, 77.04, '2025-11-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (49, 9, 52.11, '2025-09-05');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (16, 2, 65.27, '2025-04-08');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (17, 6, 71.03, '2025-01-27');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (29, 8, 30.29, '2025-11-03');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (28, 2, 31.44, '2025-11-22');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (15, 8, 63.64, '2025-02-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (4, 9, 44.82, '2025-06-26');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (29, 2, 79.38, '2025-02-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (14, 6, 40.21, '2025-01-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (16, 5, 6.41, '2025-08-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (24, 1, 66.41, '2025-11-11');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (38, 6, 32.89, '2025-07-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (17, 6, 25.05, '2025-07-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (37, 4, 93.13, '2025-08-19');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (28, 7, 12.94, '2025-07-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (30, 5, 22.82, '2025-02-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (22, 9, 90.01, '2025-06-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (32, 6, 12.57, '2025-07-13');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (15, 2, 98.24, '2025-06-06');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (5, 5, 19.82, '2025-01-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (26, 3, 77.68, '2025-01-13');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (28, 8, 85.73, '2025-10-24');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (31, 4, 26.55, '2025-02-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (14, 3, 54.34, '2025-02-26');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (47, 1, 34.73, '2024-12-18');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (30, 3, 74.69, '2024-12-19');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (34, 2, 85.02, '2025-02-01');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (32, 5, 11.87, '2025-11-24');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (45, 4, 25.76, '2025-05-06');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (41, 7, 85.98, '2025-07-18');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (47, 3, 70.12, '2025-02-01');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (50, 2, 21.35, '2025-10-19');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (31, 5, 68.2, '2025-12-06');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (6, 9, 39.59, '2025-02-18');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 9, 33.78, '2025-08-25');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (22, 3, 18.86, '2024-12-19');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (50, 1, 35.09, '2025-08-27');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (20, 9, 7.11, '2025-09-09');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (48, 2, 56.31, '2025-05-02');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (50, 2, 98.92, '2025-09-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (47, 7, 24.3, '2025-10-14');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (20, 6, 81.31, '2025-10-24');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (51, 5, 66.64, '2025-01-28');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (42, 3, 48.81, '2025-02-22');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (14, 5, 31.19, '2025-11-25');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 2, 14.56, '2025-06-22');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (38, 7, 36.91, '2025-07-15');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (37, 8, 25.85, '2025-08-23');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (6, 9, 65.73, '2025-07-26');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (39, 4, 9.09, '2025-08-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (29, 4, 89.33, '2025-11-16');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (13, 8, 95.31, '2025-08-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 7, 50.26, '2025-08-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (40, 5, 61.08, '2025-09-08');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (45, 8, 55.12, '2025-02-20');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (26, 3, 68.02, '2025-08-27');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (26, 5, 34.53, '2025-02-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (16, 5, 9.72, '2024-12-13');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (20, 6, 24.78, '2025-01-09');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (37, 6, 7.08, '2025-09-14');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (33, 3, 60.31, '2025-08-05');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (13, 7, 53.6, '2025-03-04');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (23, 8, 5.26, '2025-11-28');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (34, 9, 36.15, '2025-02-24');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (13, 1, 24.94, '2025-02-05');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (41, 7, 89.36, '2025-10-12');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (35, 3, 30.08, '2025-04-30');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (31, 9, 59.9, '2025-07-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (35, 6, 69.66, '2025-07-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (40, 9, 91.89, '2025-09-29');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (36, 6, 99.82, '2025-03-12');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (22, 7, 12.51, '2025-08-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (4, 1, 17.48, '2025-10-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (26, 5, 62.53, '2025-01-25');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (32, 4, 46.41, '2025-04-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (36, 2, 19.33, '2025-04-04');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (50, 6, 13.77, '2025-04-06');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (18, 5, 82.84, '2025-07-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (32, 5, 31.9, '2025-01-18');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (41, 7, 97.86, '2025-03-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (43, 6, 15.39, '2025-11-23');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (21, 6, 94.35, '2025-05-11');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (37, 2, 46.84, '2025-08-30');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (19, 1, 34.48, '2025-11-09');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (24, 6, 51.53, '2025-07-05');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (31, 8, 50.37, '2025-04-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (42, 4, 35.83, '2025-04-13');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (15, 3, 23.08, '2025-08-31');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (44, 7, 87.64, '2025-10-23');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (8, 4, 50.2, '2025-05-19');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (22, 7, 67.83, '2025-07-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (19, 2, 90.75, '2024-12-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (5, 1, 83.77, '2024-12-23');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (20, 7, 48.49, '2025-08-05');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 9, 50.04, '2025-07-11');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (36, 6, 24.54, '2025-02-26');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (5, 3, 10.41, '2025-11-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (37, 2, 10.92, '2025-08-24');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (12, 4, 6.15, '2025-10-05');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 6, 16.31, '2025-07-22');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (26, 5, 63.25, '2025-05-17');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 8, 66.75, '2025-01-24');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (35, 4, 34.82, '2025-11-05');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (47, 2, 19.74, '2025-01-29');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (44, 5, 59.35, '2025-10-22');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (36, 2, 92.97, '2025-01-16');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (39, 6, 61.87, '2025-04-18');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (43, 9, 24.58, '2025-04-04');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (35, 9, 57.77, '2025-10-01');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (38, 2, 39.11, '2025-09-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (48, 8, 76.93, '2025-01-04');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (26, 9, 90.94, '2025-11-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (7, 4, 54.91, '2025-10-23');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (46, 4, 89.36, '2025-08-01');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (24, 4, 80.33, '2025-11-27');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (30, 9, 81.92, '2025-09-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (20, 3, 58.02, '2025-05-02');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (5, 5, 60.99, '2025-09-11');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (15, 2, 37.08, '2025-04-06');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (9, 8, 84.81, '2025-08-08');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (16, 7, 52.73, '2025-09-06');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (2, 6, 14.19, '2025-10-02');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (4, 4, 16.85, '2025-09-14');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (9, 4, 46.03, '2025-01-20');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (8, 5, 78.16, '2025-07-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (14, 2, 9.01, '2025-07-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (6, 1, 94.14, '2025-07-08');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (7, 1, 15.25, '2025-07-08');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (45, 4, 93.75, '2025-10-07');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (48, 9, 44.66, '2025-05-04');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (21, 1, 97.94, '2025-01-22');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (51, 6, 72.17, '2025-04-28');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (33, 5, 19.36, '2025-11-03');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (49, 8, 15.79, '2025-11-14');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (14, 9, 20.4, '2025-07-25');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (32, 7, 78.97, '2025-02-27');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (31, 4, 37.43, '2025-02-19');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (5, 2, 11.18, '2025-07-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 3, 21.9, '2025-08-14');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (46, 7, 67.08, '2025-03-02');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (50, 2, 21.07, '2025-04-19');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (50, 3, 77.79, '2025-10-10');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (28, 9, 78.39, '2025-05-24');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (11, 7, 48.9, '2025-04-30');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (45, 9, 44.08, '2025-12-08');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (45, 4, 16.07, '2025-10-31');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (45, 5, 31.27, '2025-06-03');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (44, 4, 55.0, '2025-10-21');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (41, 7, 36.68, '2025-03-11');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (51, 4, 89.35, '2025-06-18');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (7, 6, 46.62, '2025-07-27');
INSERT INTO transport_entries (user_id, transport_id, distance_km, date) VALUES (27, 7, 50.02, '2025-02-13');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (26, 6, 17.51, '2025-09-28');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (43, 4, 18.59, '2025-02-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (6, 5, 2.34, '2025-06-25');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (8, 4, 10.67, '2024-12-20');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (42, 7, 5.53, '2025-11-04');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (7, 2, 11.61, '2025-07-06');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (13, 3, 8.91, '2025-03-20');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (50, 3, 18.83, '2025-09-28');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (51, 1, 19.68, '2025-03-03');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (9, 1, 8.31, '2025-07-28');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (50, 3, 13.23, '2025-09-14');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (39, 7, 9.14, '2025-01-21');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (10, 3, 8.69, '2025-03-18');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (4, 5, 2.06, '2025-08-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (10, 3, 7.5, '2025-11-01');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (4, 6, 12.9, '2025-05-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (17, 7, 15.91, '2025-09-27');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (35, 1, 19.67, '2025-09-02');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (11, 4, 1.74, '2025-07-11');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (16, 2, 2.36, '2025-01-30');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (45, 3, 7.24, '2025-05-01');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (23, 3, 9.39, '2025-07-10');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (18, 2, 19.2, '2025-11-05');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (19, 3, 12.35, '2025-02-13');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (29, 1, 14.91, '2025-02-25');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (50, 5, 12.27, '2025-07-22');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (40, 5, 12.37, '2025-11-17');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (18, 1, 13.93, '2025-10-29');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (44, 3, 16.57, '2025-08-15');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (8, 1, 18.62, '2024-12-20');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (16, 2, 17.53, '2025-07-06');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (19, 5, 1.37, '2025-03-05');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (11, 7, 8.15, '2025-04-03');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (40, 5, 12.33, '2025-09-05');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (11, 5, 13.92, '2025-05-28');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (37, 4, 8.25, '2025-10-25');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (20, 1, 19.99, '2025-07-01');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (13, 5, 1.37, '2025-06-11');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (15, 2, 1.04, '2025-01-17');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (49, 1, 15.55, '2025-06-12');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (13, 3, 7.3, '2025-10-30');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (15, 1, 11.45, '2025-09-02');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (14, 6, 10.5, '2025-05-22');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (3, 3, 15.72, '2025-05-29');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (12, 3, 7.89, '2025-02-03');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (4, 6, 4.07, '2025-08-25');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (22, 4, 13.88, '2025-04-22');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (32, 2, 6.48, '2025-05-06');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (26, 7, 14.4, '2025-07-04');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (33, 4, 4.97, '2025-05-09');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (39, 1, 17.69, '2025-03-02');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (9, 3, 2.19, '2025-10-18');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (46, 2, 13.39, '2024-12-14');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (20, 7, 2.86, '2025-03-01');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (7, 1, 12.66, '2025-04-18');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (33, 5, 11.09, '2025-05-17');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (41, 7, 16.02, '2025-01-20');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (8, 1, 2.49, '2025-12-04');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (34, 1, 10.7, '2025-07-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (32, 7, 14.86, '2025-08-27');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (17, 5, 2.07, '2025-04-08');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (10, 1, 1.47, '2025-01-04');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (3, 5, 0.76, '2024-12-13');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (20, 4, 18.48, '2025-03-24');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (47, 4, 15.66, '2024-12-26');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (4, 2, 19.86, '2025-08-03');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (23, 6, 6.25, '2025-01-06');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (11, 1, 8.19, '2025-11-24');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (15, 3, 8.25, '2025-04-30');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (38, 1, 13.46, '2025-03-23');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (3, 6, 6.21, '2025-11-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (30, 5, 18.82, '2025-06-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (43, 4, 6.86, '2025-06-21');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (50, 7, 4.49, '2025-07-04');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (28, 7, 2.08, '2025-05-10');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (10, 4, 11.75, '2025-10-02');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (49, 3, 13.38, '2025-10-22');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (40, 7, 3.74, '2025-08-06');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (9, 6, 8.66, '2025-08-03');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (31, 2, 5.98, '2025-10-12');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (23, 1, 9.48, '2025-05-10');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (19, 3, 12.72, '2025-06-01');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (36, 3, 7.65, '2025-07-17');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (38, 1, 12.21, '2025-10-11');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (49, 4, 1.43, '2025-07-09');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (8, 6, 12.2, '2025-06-08');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (44, 5, 10.76, '2025-07-23');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (45, 3, 18.71, '2025-04-05');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (23, 1, 4.06, '2024-12-19');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (13, 6, 7.16, '2025-06-04');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (15, 7, 19.1, '2025-04-03');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (9, 3, 2.26, '2024-12-28');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (19, 5, 10.54, '2025-06-26');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (15, 5, 13.25, '2025-06-01');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (40, 1, 5.02, '2025-10-12');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (9, 7, 0.55, '2025-11-27');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (50, 3, 13.84, '2025-10-24');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (6, 5, 7.53, '2025-06-22');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (2, 6, 18.89, '2025-08-31');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (43, 2, 9.29, '2025-04-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (5, 4, 19.25, '2025-07-29');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (27, 4, 11.7, '2025-09-15');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (33, 5, 12.53, '2025-05-28');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (9, 2, 13.1, '2025-02-21');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (18, 3, 19.17, '2025-03-04');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (32, 4, 6.84, '2025-04-13');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (4, 7, 11.88, '2025-08-11');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (33, 7, 14.4, '2025-02-18');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (17, 5, 5.11, '2025-06-29');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (19, 6, 1.42, '2025-11-15');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (16, 4, 18.73, '2025-06-23');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (34, 3, 18.34, '2025-02-11');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (29, 3, 2.71, '2025-08-22');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (13, 7, 8.13, '2025-03-22');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (3, 3, 19.8, '2025-03-30');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (9, 3, 10.31, '2025-03-27');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (46, 1, 17.44, '2025-07-26');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (4, 6, 17.34, '2025-07-12');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (49, 2, 10.98, '2025-10-26');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (32, 5, 5.88, '2025-09-27');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (21, 7, 1.45, '2025-04-25');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (18, 3, 3.79, '2025-05-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (35, 6, 6.95, '2025-08-23');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (16, 2, 16.63, '2025-09-22');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (37, 6, 13.32, '2025-01-15');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (45, 4, 2.76, '2025-01-04');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (41, 5, 15.92, '2025-02-08');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (29, 4, 2.37, '2025-12-03');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (44, 5, 4.37, '2025-03-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (15, 7, 6.06, '2025-09-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (30, 1, 19.98, '2025-06-28');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (4, 3, 11.19, '2024-12-08');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (38, 1, 13.49, '2025-07-18');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (32, 6, 12.0, '2025-04-14');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (4, 1, 7.21, '2025-04-23');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (46, 3, 12.11, '2025-10-14');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (2, 2, 13.32, '2025-12-03');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (11, 3, 10.87, '2025-04-01');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (4, 7, 11.28, '2025-05-31');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (19, 3, 6.26, '2025-10-19');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (41, 1, 14.96, '2025-01-08');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (30, 1, 1.16, '2025-02-10');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (19, 5, 11.82, '2025-01-01');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (20, 7, 16.33, '2025-05-25');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (24, 2, 3.51, '2025-09-07');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (38, 1, 6.89, '2025-04-27');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (37, 6, 18.8, '2025-06-30');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (45, 4, 18.64, '2025-11-03');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (17, 7, 9.24, '2025-03-28');
INSERT INTO waste_entries (user_id, waste_type_id, weight_kg, date) VALUES (9, 3, 12.3, '2025-06-18');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (50, 'Random Location', 32.774049, 3.983122, 'Found some trash here', 'pending');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (18, 'Random Location', 37.159077, 9.549303, 'Found some trash here', 'resolved');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (43, 'Random Location', 38.283674, 3.28176, 'Found some trash here', 'pending');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (46, 'Random Location', 43.770824, -9.061903, 'Found some trash here', 'pending');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (40, 'Random Location', 35.323198, 0.215905, 'Found some trash here', 'resolved');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (15, 'Random Location', 36.202667, 4.117007, 'Found some trash here', 'pending');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (40, 'Random Location', 36.68718, -2.908642, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (28, 'Random Location', 31.536723, -3.020366, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (7, 'Random Location', 43.685717, -8.49251, 'Found some trash here', 'pending');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (19, 'Random Location', 33.776147, -5.163154, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (23, 'Random Location', 32.66859, -2.461755, 'Found some trash here', 'pending');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (32, 'Random Location', 43.428747, 6.495963, 'Found some trash here', 'pending');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (24, 'Random Location', 30.390067, 6.891397, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (37, 'Random Location', 36.489665, 0.974395, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (39, 'Random Location', 42.155644, -2.363148, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (28, 'Random Location', 44.291586, -0.672903, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (11, 'Random Location', 42.442583, 3.361536, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (22, 'Random Location', 40.303234, 0.779985, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (23, 'Random Location', 44.542156, 8.546384, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (51, 'Random Location', 43.606025, -9.366854, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (40, 'Random Location', 40.458857, -9.141957, 'Found some trash here', 'resolved');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (30, 'Random Location', 32.7441, -4.07413, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (48, 'Random Location', 37.910917, -7.768928, 'Found some trash here', 'resolved');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (50, 'Random Location', 38.472308, -7.773463, 'Found some trash here', 'resolved');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (41, 'Random Location', 42.013013, 1.230659, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (29, 'Random Location', 35.191, -2.596728, 'Found some trash here', 'resolved');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (46, 'Random Location', 40.494877, -8.058845, 'Found some trash here', 'resolved');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (39, 'Random Location', 31.712017, 0.817505, 'Found some trash here', 'pending');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (42, 'Random Location', 43.078635, -0.033513, 'Found some trash here', 'rejected');
INSERT INTO trash_reports (reporter_id, location_description, latitude, longitude, description, status) VALUES (14, 'Random Location', 34.153877, -4.096528, 'Found some trash here', 'resolved');