<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
}

try {
    $conn = getDBConnection();
    $user_id = getCurrentUserId();
    
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        sendJsonResponse([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'firstName' => $user['first_name'],
                'lastName' => $user['last_name'],
                'email' => $user['email']
            ]
        ]);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'User not found'], 404);
    }
} catch (Exception $e) {
    error_log("Get User Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Error fetching user data: ' . $e->getMessage()], 500);
} 