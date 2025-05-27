<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необхідно авторизуватися']);
    exit;
}

$id = $_POST['id'] ?? null;
$brand = $_POST['brand'] ?? '';
$model = $_POST['model'] ?? '';
$year = $_POST['year'] ?? '';
$mileage = $_POST['mileage'] ?? '';
$fuel_type = $_POST['fuel_type'] ?? '';
$transmission = $_POST['transmission'] ?? '';
$location = $_POST['location'] ?? '';
$price = $_POST['price'] ?? '';
$currency = $_POST['currency'] ?? '';
$description = $_POST['description'] ?? '';

if (!$id || !$brand || !$model || !$year || !$mileage || !$fuel_type || !$transmission || !$location || !$price || !$currency || !$description) {
    echo json_encode(['success' => false, 'message' => 'Всі поля обов\'язкові для заповнення']);
    exit;
}

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT user_id FROM cars WHERE id = ?");
    $stmt->execute([$id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$car || $car['user_id'] != getCurrentUserId()) {
        echo json_encode(['success' => false, 'message' => 'Немає доступу до цього оголошення']);
        exit;
    }
    
    $stmt = $conn->prepare("
        UPDATE cars 
        SET brand = ?, model = ?, year = ?, mileage = ?, fuel_type = ?, 
            transmission = ?, location = ?, price = ?, currency = ?, description = ?
        WHERE id = ? AND user_id = ?
    ");
    
    $stmt->execute([
        $brand, $model, $year, $mileage, $fuel_type, 
        $transmission, $location, $price, $currency, $description,
        $id, getCurrentUserId()
    ]);
    
    if (!empty($_FILES['photos']['name'][0])) {
        $upload_dir = '../uploads/';
        
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Не вдалося створити директорію для завантаження");
            }
        }
        
        if (!is_writable($upload_dir)) {
            throw new Exception("Немає прав на запис у директорію uploads");
        }
        
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['photos']['name'][$key];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                if (!in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
                    continue;
                }
                
                $new_file_name = uniqid() . '.' . $file_ext;
                $target_path = $upload_dir . $new_file_name;
                
                if (!move_uploaded_file($tmp_name, $target_path)) {
                    throw new Exception("Помилка під час завантаження файлу: " . error_get_last()['message']);
                }
                
                $stmt = $conn->prepare("INSERT INTO photos (car_id, filename, user_id) VALUES (?, ?, ?)");
                if (!$stmt->execute([$id, $new_file_name, getCurrentUserId()])) {
                    throw new Exception("Помилка під час додавання запису в базу даних");
                }
            } else {
                throw new Exception("Помилка завантаження файлу: " . $_FILES['photos']['error'][$key]);
            }
        }
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 