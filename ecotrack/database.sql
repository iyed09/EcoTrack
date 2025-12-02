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
