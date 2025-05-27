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
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = :user_id AND car_id = :car_id");
    $user_id = getCurrentUserId();
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':car_id', $car_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        sendJsonResponse(['success' => true, 'message' => 'Оголошення видалено з обраного']);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Оголошення не знайдено в обраному'], 404);
    }
} catch (Exception $e) {
    error_log("Remove from Favorites Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Помилка під час видалення з обраного: ' . $e->getMessage()], 500);
} 