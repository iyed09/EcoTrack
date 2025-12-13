<?php
require_once 'config.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'user',
            avatar VARCHAR(255) DEFAULT NULL,
            total_points INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS energy_sources (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            emission_factor DECIMAL(10,4) NOT NULL,
            unit VARCHAR(50) NOT NULL
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS energy_consumption (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            source_id INTEGER REFERENCES energy_sources(id),
            amount DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS transport_types (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            emission_per_km DECIMAL(10,4) NOT NULL,
            icon VARCHAR(50),
            is_eco_friendly BOOLEAN DEFAULT FALSE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS transport_entries (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            transport_id INTEGER REFERENCES transport_types(id),
            distance_km DECIMAL(10,2) NOT NULL,
            date DATE NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_categories (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            eco_impact_score DECIMAL(5,2) NOT NULL
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS purchases (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            category_id INTEGER REFERENCES product_categories(id),
            product_name VARCHAR(200) NOT NULL,
            quantity INTEGER DEFAULT 1,
            is_eco_friendly BOOLEAN DEFAULT FALSE,
            date DATE NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS waste_types (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            recyclable BOOLEAN DEFAULT FALSE,
            impact_score DECIMAL(5,2) NOT NULL
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS waste_entries (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            waste_type_id INTEGER REFERENCES waste_types(id),
            weight_kg DECIMAL(10,2) NOT NULL,
            properly_disposed BOOLEAN DEFAULT TRUE,
            date DATE NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS trash_reports (
            id SERIAL PRIMARY KEY,
            reporter_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
            location_description TEXT NOT NULL,
            latitude DECIMAL(10,8),
            longitude DECIMAL(11,8),
            description TEXT NOT NULL,
            photo_path VARCHAR(255),
            status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_content (
            id SERIAL PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            content TEXT NOT NULL,
            type VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS points_history (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            points INTEGER NOT NULL,
            action_type VARCHAR(50) NOT NULL,
            action_description TEXT,
            reference_id INTEGER,
            reference_type VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS achievements (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            icon VARCHAR(50) NOT NULL,
            badge_color VARCHAR(50) DEFAULT 'primary',
            points_required INTEGER DEFAULT 0,
            action_type VARCHAR(50),
            action_count INTEGER DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_achievements (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            achievement_id INTEGER REFERENCES achievements(id) ON DELETE CASCADE,
            earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, achievement_id)
        )
    ");

    $checkEnergy = $pdo->query("SELECT COUNT(*) FROM energy_sources")->fetchColumn();
    if ($checkEnergy == 0) {
        $pdo->exec("
            INSERT INTO energy_sources (name, emission_factor, unit) VALUES
            ('Electricity', 0.4, 'kWh'),
            ('Natural Gas', 2.0, 'm³'),
            ('Heating Oil', 2.5, 'L'),
            ('Solar Panel', 0.05, 'kWh'),
            ('Wind Energy', 0.02, 'kWh')
        ");
    }

    $checkTransport = $pdo->query("SELECT COUNT(*) FROM transport_types")->fetchColumn();
    if ($checkTransport == 0) {
        $pdo->exec("
            INSERT INTO transport_types (name, emission_per_km, icon, is_eco_friendly) VALUES
            ('Car (Gasoline)', 0.21, 'bi-car-front', FALSE),
            ('Car (Diesel)', 0.17, 'bi-car-front', FALSE),
            ('Electric Car', 0.05, 'bi-ev-front', TRUE),
            ('Bus', 0.09, 'bi-bus-front', TRUE),
            ('Train', 0.04, 'bi-train-front', TRUE),
            ('Bicycle', 0.0, 'bi-bicycle', TRUE),
            ('Walking', 0.0, 'bi-person-walking', TRUE),
            ('Motorcycle', 0.10, 'bi-scooter', FALSE),
            ('Airplane', 0.25, 'bi-airplane', FALSE)
        ");
    }

    $checkProducts = $pdo->query("SELECT COUNT(*) FROM product_categories")->fetchColumn();
    if ($checkProducts == 0) {
        $pdo->exec("
            INSERT INTO product_categories (name, eco_impact_score) VALUES
            ('Electronics', 8.5),
            ('Clothing', 5.0),
            ('Food - Local', 2.0),
            ('Food - Imported', 6.0),
            ('Household Items', 4.0),
            ('Personal Care', 3.5),
            ('Packaging', 7.0)
        ");
    }

    $checkWaste = $pdo->query("SELECT COUNT(*) FROM waste_types")->fetchColumn();
    if ($checkWaste == 0) {
        $pdo->exec("
            INSERT INTO waste_types (name, recyclable, impact_score) VALUES
            ('Plastic', TRUE, 8.0),
            ('Paper', TRUE, 3.0),
            ('Glass', TRUE, 4.0),
            ('Metal', TRUE, 5.0),
            ('Organic', TRUE, 2.0),
            ('Electronic Waste', TRUE, 9.0),
            ('General Waste', FALSE, 7.0)
        ");
    }

    $checkAchievements = $pdo->query("SELECT COUNT(*) FROM achievements")->fetchColumn();
    if ($checkAchievements == 0) {
        $pdo->exec("
            INSERT INTO achievements (name, description, icon, badge_color, points_required, action_type, action_count) VALUES
            ('First Steps', 'Create your account and join the eco community', 'bi-person-check', 'success', 0, 'register', 1),
            ('First Reporter', 'Submit your first trash report', 'bi-flag', 'danger', 0, 'trash_report', 1),
            ('Waste Warrior', 'Log 10 waste disposal entries', 'bi-trash', 'purple', 0, 'waste_entry', 10),
            ('Energy Tracker', 'Log 10 energy consumption entries', 'bi-lightning-charge', 'warning', 0, 'energy_entry', 10),
            ('Road Runner', 'Log 10 transport entries', 'bi-car-front', 'info', 0, 'transport_entry', 10),
            ('Eco Champion', 'Use eco-friendly transport 20 times', 'bi-bicycle', 'success', 0, 'eco_transport', 20),
            ('Point Collector', 'Earn 100 eco-points', 'bi-star', 'warning', 100, NULL, 0),
            ('Eco Expert', 'Earn 500 eco-points', 'bi-trophy', 'primary', 500, NULL, 0),
            ('Planet Protector', 'Earn 1000 eco-points', 'bi-globe-americas', 'success', 1000, NULL, 0),
            ('Community Hero', 'Submit 10 trash reports', 'bi-shield-check', 'danger', 0, 'trash_report', 10),
            ('Recycling Master', 'Properly dispose recyclable waste 20 times', 'bi-recycle', 'success', 0, 'recycle', 20),
            ('Green Commuter', 'Log 50 eco-friendly transport trips', 'bi-ev-front', 'info', 0, 'eco_transport', 50)
        ");
    }

    $checkAdmin = $pdo->query("SELECT COUNT(*) FROM users WHERE email = 'admin@ecotrack.com'")->fetchColumn();
    if ($checkAdmin == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, total_points) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Admin', 'admin@ecotrack.com', $adminPassword, 'admin', 0]);
    }

    echo "Database initialized successfully!";
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>
