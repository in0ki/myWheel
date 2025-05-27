<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необхідно авторизуватися']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$filename = $data['filename'] ?? null;
$listing_id = $data['listing_id'] ?? null;

if (!$filename || !$listing_id) {
    echo json_encode(['success' => false, 'message' => 'Необхідні дані відсутні']);
    exit;
}

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT user_id FROM cars WHERE id = ?");
    $stmt->execute([$listing_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$car || $car['user_id'] != getCurrentUserId()) {
        echo json_encode(['success' => false, 'message' => 'Немає доступу до цього оголошення']);
        exit;
    }
 
    $stmt = $conn->prepare("DELETE FROM photos WHERE car_id = ? AND filename = ?");
    $stmt->execute([$listing_id, $filename]);
    
    $file_path = '../uploads/' . $filename;
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Помилка при видаленні фотографії']);
} 