<?php
require_once 'includes/config.php';

try {
    // Create tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'user',
            avatar VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS energy_sources (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            emission_factor DECIMAL(10,4) NOT NULL,
            unit VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

        CREATE TABLE IF NOT EXISTS transport_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            emission_per_km DECIMAL(10,4) NOT NULL,
            icon VARCHAR(50)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

        CREATE TABLE IF NOT EXISTS product_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            eco_impact_score DECIMAL(5,2) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

        CREATE TABLE IF NOT EXISTS waste_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            recyclable BOOLEAN DEFAULT FALSE,
            impact_score DECIMAL(5,2) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

        CREATE TABLE IF NOT EXISTS admin_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            content TEXT NOT NULL,
            type VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Insert seed data if not exists
    $check = $pdo->query("SELECT COUNT(*) FROM energy_sources")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("
            INSERT INTO energy_sources (name, emission_factor, unit) VALUES
            ('Electricity', 0.4, 'kWh'),
            ('Natural Gas', 2.0, 'm³'),
            ('Heating Oil', 2.5, 'L'),
            ('Solar Panel', 0.05, 'kWh'),
            ('Wind Energy', 0.02, 'kWh');

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

            INSERT INTO product_categories (name, eco_impact_score) VALUES
            ('Electronics', 8.5),
            ('Clothing', 5.0),
            ('Food - Local', 2.0),
            ('Food - Imported', 6.0),
            ('Household Items', 4.0),
            ('Personal Care', 3.5),
            ('Packaging', 7.0);

            INSERT INTO waste_types (name, recyclable, impact_score) VALUES
            ('Plastic', TRUE, 8.0),
            ('Paper', TRUE, 3.0),
            ('Glass', TRUE, 4.0),
            ('Metal', TRUE, 5.0),
            ('Organic', TRUE, 2.0),
            ('Electronic Waste', TRUE, 9.0),
            ('General Waste', FALSE, 7.0);
        ");
    }

    // Insert admin user if not exists
    $adminCheck = $pdo->query("SELECT COUNT(*) FROM users WHERE email = 'admin@ecotrack.com'")->fetchColumn();
    if ($adminCheck == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@ecotrack.com', '$adminPassword', 'admin')");
    }

    echo "<div style='font-family: Arial; padding: 20px;'>";
    echo "<h1 style='color: green;'>✓ Database Setup Complete!</h1>";
    echo "<p><strong>Admin Credentials:</strong></p>";
    echo "<p>Email: <code>admin@ecotrack.com</code></p>";
    echo "<p>Password: <code>admin123</code></p>";
    echo "<p><a href='modules/auth/login.php' style='padding: 10px 20px; background-color: #4caf50; color: white; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family: Arial; padding: 20px; color: red;'>";
    echo "<h1>✗ Setup Failed</h1>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
