<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Необхідна авторизація'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Метод не підтримується'], 405);
}

$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$show_email = isset($_POST['show_email']) ? 1 : 0;

$errors = [];

if (empty($first_name)) $errors[] = 'Ім`я обов`язково';
if (empty($last_name)) $errors[] = 'Прізвище обов`язкове';

if (!empty($errors)) {
    sendJsonResponse(['success' => false, 'message' => implode(', ', $errors)], 400);
}

try {
    $conn = getDBConnection();
    $user_id = getCurrentUserId();
    
    $profile_photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $allowed_types = ['image/jpeg', 'image/png'];
        
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Непідтримуваний формат файлу. Використовуйте JPEG або PNG.');
        }
        
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            throw new Exception('Розмір файлу перевищує 5MB');
        }
      
        $upload_dir = __DIR__ . '/../uploads/profiles';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $upload_dir . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Помилка під час завантаження файлу');
        }
        
        $profile_photo = $filename;
    }
    
    $query = "UPDATE users SET 
              first_name = :first_name,
              last_name = :last_name,
              phone = :phone,
              show_email = :show_email";
    
    $params = [
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':phone' => $phone,
        ':show_email' => $show_email,
        ':id' => $user_id
    ];
    
    if ($profile_photo) {
        $query .= ", profile_photo = :profile_photo";
        $params[':profile_photo'] = $profile_photo;
    }
    
    $query .= " WHERE id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Профіль успішно оновлено'
    ]);
    
} catch (Exception $e) {
    error_log("Error in update_profile.php: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Помилка під час оновлення профілю: ' . $e->getMessage()
    ], 500);
} 