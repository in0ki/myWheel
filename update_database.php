<?php
require_once 'config/database.php';

try {
    $conn = getDBConnection();
    
    $tables = $conn->query("SHOW TABLES LIKE 'cars'")->fetchAll();
    if (count($tables) === 0) {
        $sql = "CREATE TABLE cars (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            brand VARCHAR(100) NOT NULL,
            model VARCHAR(100) NOT NULL,
            year INT(4) NOT NULL,
            mileage INT(11) NOT NULL,
            fuel_type VARCHAR(50) NOT NULL,
            transmission VARCHAR(50) NOT NULL,
            location VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            currency VARCHAR(10) NOT NULL DEFAULT 'USD',
            description TEXT NOT NULL,
            is_featured TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (user_id),
            INDEX (brand),
            INDEX (model),
            INDEX (is_featured),
            INDEX (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $conn->exec($sql);
        echo "Таблица cars успешно создана.<br>";
    } else {
        $columns = $conn->query("SHOW COLUMNS FROM cars LIKE 'is_active'")->fetchAll();
        if (count($columns) === 0) {
            $sql = "ALTER TABLE cars ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER is_featured";
            $conn->exec($sql);
            echo "Колонка is_active успішно додана в таблицю cars.<br>";
        }
    }
    
    $tables = $conn->query("SHOW TABLES LIKE 'photos'")->fetchAll();
    if (count($tables) === 0) {
        $sql = "CREATE TABLE photos (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            user_id INT(11) NOT NULL,
            car_id INT(11) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id),
            INDEX (car_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $conn->exec($sql);
        echo "Таблиця photos успішно створена.<br>";
    } else {
        echo "Таблиця photos вже існує.<br>";
    }
    
    $tables = $conn->query("SHOW TABLES LIKE 'favorites'")->fetchAll();
    if (count($tables) === 0) {
        $sql = "CREATE TABLE favorites (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            car_id INT(11) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id),
            INDEX (car_id),
            UNIQUE KEY unique_favorite (user_id, car_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $conn->exec($sql);
        echo "Таблиця favorites успішно створена.<br>";
    } else {
        echo "Таблиця favorites вже існує.<br>";
    }
    
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'show_email'")->fetchAll();
    if (count($columns) === 0) {
        $sql = "ALTER TABLE users ADD COLUMN show_email TINYINT(1) NOT NULL DEFAULT 0 AFTER phone";
        $conn->exec($sql);
        echo "Колонку show_email успішно додано в таблицю users.<br>";
    } else {
        echo "Колонка show_email уже існує.<br>";
    }
    
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_photo'")->fetchAll();
    if (count($columns) === 0) {
        $sql = "ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL AFTER show_email";
        $conn->exec($sql);
        echo "Колонку profile_photo успішно додано в таблицю users.<br>";
    } else {
        echo "Колонка profile_photo вже існує.<br>";
    }
    
    $uploads_dir = __DIR__ . '/uploads';
    if (!file_exists($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
        echo "Директорію uploads успішно створено.<br>";
    } else {
        echo "Директорія uploads вже існує.<br>";
    }
    
    $profiles_dir = __DIR__ . '/uploads/profiles';
    if (!file_exists($profiles_dir)) {
        mkdir($profiles_dir, 0777, true);
        echo "Директорію uploads/profiles успішно створено.<br>";
    } else {
        echo "Директорія uploads/profiles вже існує.<br>";
    }
    
} catch (PDOException $e) {
    echo "Помилка під час оновлення бази даних: " . $e->getMessage();
} 