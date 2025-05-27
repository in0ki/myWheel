<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT c.id, c.brand, c.model, c.year, c.mileage, c.price, c.currency,
               (SELECT filename FROM photos WHERE car_id = c.id LIMIT 1) as image_url
        FROM cars c
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    
    $cars = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cars[] = [
            'id' => $row['id'],
            'title' => $row['brand'] . ' ' . $row['model'],
            'year' => $row['year'],
            'mileage' => number_format($row['mileage'], 0, ',', ' '),
            'price' => number_format($row['price'], 0, ',', ' '),
            'currency' => $row['currency'],
            'image_url' => $row['image_url'] ? 'uploads/' . $row['image_url'] : 'images/no-image.png'
        ];
    }
    
    sendJsonResponse(['success' => true, 'cars' => $cars]);
} catch (Exception $e) {
    error_log("Featured Cars Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Помилка під час завантаження обраних автомобілів: ' . $e->getMessage()], 500);
} 