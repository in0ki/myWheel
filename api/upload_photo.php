<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Необхідна авторизація'], 401);
}

if (!isset($_FILES['file'])) {
    sendJsonResponse(['success' => false, 'message' => 'Файл не знайдено'], 400);
}

$file = $_FILES['file'];

$allowedTypes = ['image/jpeg', 'image/png'];
if (!in_array($file['type'], $allowedTypes)) {
    sendJsonResponse(['success' => false, 'message' => 'Непідтримуваний формат файлу'], 400);
}

if ($file['size'] > 5 * 1024 * 1024) {
    sendJsonResponse(['success' => false, 'message' => 'Файл занадто великий'], 400);
}

$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;
$filepath = $uploadDir . $filename;

try {
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO photos (filename, user_id) VALUES (:filename, :user_id)");
        $user_id = getCurrentUserId();
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            $fileId = $conn->lastInsertId();
            sendJsonResponse([
                'success' => true,
                'photo_id' => $fileId,
                'filename' => $filename
            ]);
        } else {
            unlink($filepath);
            sendJsonResponse(['success' => false, 'message' => 'Помилка під час збереження файлу'], 500);
        }
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Помилка під час завантаження файлу'], 500);
    }
} catch (Exception $e) {
    error_log("Upload Photo Error: " . $e->getMessage());
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    sendJsonResponse(['success' => false, 'message' => 'Помилка під час завантаження файлу: ' . $e->getMessage()], 500);
} 