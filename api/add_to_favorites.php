<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Необхідна авторизація'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Метод не підтримується'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$car_id = $data['car_id'] ?? null;

if (!$car_id) {
    sendJsonResponse(['success' => false, 'message' => 'ID автомобіля не вказано'], 400);
}

try {
    $conn = getDBConnection();
    $user_id = getCurrentUserId();
    
    $stmt = $conn->prepare("SELECT id FROM cars WHERE id = :car_id");
    $stmt->bindParam(':car_id', $car_id);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'Автомобіль не знайдено'], 404);
    }
    
    $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = :user_id AND car_id = :car_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':car_id', $car_id);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'Автомобіль уже додано до обраного'], 400);
    }
    
    $stmt = $conn->prepare("INSERT INTO favorites (user_id, car_id, created_at) VALUES (:user_id, :car_id, NOW())");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':car_id', $car_id);
    $stmt->execute();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Автомобіль додано до обраного'
    ]);
    
} catch (Exception $e) {
    error_log("Error in add_to_favorites.php: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Помилка під час додавання в обране: ' . $e->getMessage()
    ], 500);
} 