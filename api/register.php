<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$data = json_decode(file_get_contents('php://input'), true);

$required_fields = ['firstName', 'lastName', 'email', 'password'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        sendJsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
    }
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
}

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $data['email']);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        sendJsonResponse(['success' => false, 'message' => 'Email already registered'], 400);
    }
    
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (:firstName, :lastName, :email, :password)");
    $stmt->bindParam(':firstName', $data['firstName']);
    $stmt->bindParam(':lastName', $data['lastName']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':password', $hashed_password);
    
    if ($stmt->execute()) {
        sendJsonResponse(['success' => true]);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Registration failed'], 500);
    }
} catch (Exception $e) {
    error_log("Registration Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()], 500);
} 