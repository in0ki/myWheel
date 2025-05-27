<?php
require_once 'config.php';

if (!isLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_messages':
        $stmt = $conn->prepare("
            SELECT m.*, 
                   CASE WHEN m.sender_id = ? THEN 1 ELSE 0 END as is_own
            FROM messages m
            WHERE m.sender_id = ? OR m.receiver_id = ?
            ORDER BY m.created_at ASC
        ");
        $user_id = getCurrentUserId();
        $stmt->bind_param("iii", $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = [
                'text' => $row['message'],
                'time' => date('H:i', strtotime($row['created_at'])),
                'is_own' => (bool)$row['is_own']
            ];
        }
        
        sendJsonResponse(['success' => true, 'messages' => $messages]);
        break;

    case 'send_message':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['message']) || empty($data['message'])) {
            sendJsonResponse(['success' => false, 'message' => 'Message is required'], 400);
        }

        $admin_id = 1;
        $user_id = getCurrentUserId();
        
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $admin_id, $data['message']);
        
        if ($stmt->execute()) {
            sendJsonResponse(['success' => true]);
        } else {
            sendJsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
        }
        break;

    default:
        sendJsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
} 