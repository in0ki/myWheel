<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    sendJsonResponse(['success' => false, 'message' => 'Email and password are required'], 400);
}

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, password, first_name, last_name FROM users WHERE email = :email");
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid email or password'], 401);
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify($data['password'], $user['password'])) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid email or password'], 401);
    }
  
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    
    sendJsonResponse(['success' => true]);
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()], 500);
} 