<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';


header('Content-Type: application/json');

if (!isLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Необхідна авторизація'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Метод не підтримується'], 405);
}

$listing_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $listing_id = $_GET['id'] ?? null;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $listing_id = $data['id'] ?? null;
}

if (!$listing_id) {
    sendJsonResponse(['success' => false, 'message' => 'ID оголошення не вказано'], 400);
}

try {
    $conn = getDBConnection();
    $user_id = getCurrentUserId();
    
    $stmt = $conn->prepare("SELECT id FROM cars WHERE id = :listing_id AND user_id = :user_id");
    $stmt->bindParam(':listing_id', $listing_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        sendJsonResponse(['success' => false, 'message' => 'Оголошення не знайдено або у вас немає прав на його видалення'], 403);
    }
    
    $conn->beginTransaction();
    
    try {
        $stmt = $conn->prepare("SELECT filename FROM photos WHERE car_id = :listing_id");
        $stmt->bindParam(':listing_id', $listing_id);
        $stmt->execute();
        $photos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($photos as $photo) {
            $filepath = __DIR__ . '/../uploads/' . $photo;
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        $stmt = $conn->prepare("DELETE FROM photos WHERE car_id = :listing_id");
        $stmt->bindParam(':listing_id', $listing_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM cars WHERE id = :listing_id");
        $stmt->bindParam(':listing_id', $listing_id);
        $stmt->execute();
        
        $conn->commit();
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Оголошення успішно видалено'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error in delete_listing.php: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Помилка під час видалення оголошення: ' . $e->getMessage()
    ], 500);
} 