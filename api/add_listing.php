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

$brand = $_POST['brand'] ?? '';
$model = $_POST['model'] ?? '';
$year = $_POST['year'] ?? '';
$mileage = $_POST['mileage'] ?? '';
$fuelType = $_POST['fuelType'] ?? '';
$transmission = $_POST['transmission'] ?? '';
$location = $_POST['location'] ?? '';
$price = $_POST['price'] ?? '';
$currency = $_POST['currency'] ?? '';
$description = $_POST['description'] ?? '';
$photos = $_POST['photos'] ?? '[]';
$photos = json_decode($photos, true);

$errors = [];

if (empty($brand)) $errors[] = 'Марка автомобіля обов`язкова';
if (empty($model)) $errors[] = 'Модель автомобіля обов`язкова';
if (empty($year)) $errors[] = 'Рік випуску обов`язковий';
if (!is_numeric($year) || $year < 1950 || $year > date('Y')) {
    $errors[] = 'Некоректний рік випуску';
}
if (empty($mileage)) $errors[] = 'Пробіг обов`язковий';
if (!is_numeric($mileage) || $mileage < 0) {
    $errors[] = 'Некоректний пробіг';
}
if (empty($fuelType)) $errors[] = 'Тип палива обов`язковий';
if (empty($transmission)) $errors[] = 'Тип коробки передач обов`язковий';
if (empty($location)) $errors[] = 'Місцезнаходження обов`язкове';
if (empty($price)) $errors[] = 'Ціна обов`язкова';
if (!is_numeric($price) || $price < 1000) {
    $errors[] = 'Некоректна ціна';
}
if (empty($description)) $errors[] = 'Опис обов`язковий';
if (strlen($description) > 1000) {
    $errors[] = 'Опис занадто довгий';
}

if (json_last_error() !== JSON_ERROR_NONE) {
    $errors[] = 'Помилка під час опрацювання даних фотографій';
}

if (!is_array($photos)) {
    $errors[] = 'Некоректний формат даних фотографій';
}

if (!empty($errors)) {
    sendJsonResponse(['success' => false, 'message' => implode(', ', $errors)], 400);
}

try {
    $conn = getDBConnection();
    
    $conn->beginTransaction();

    $stmt = $conn->prepare("
        INSERT INTO cars (
            user_id, brand, model, year, mileage, fuel_type, 
            transmission, location, price, currency, description
        ) VALUES (:user_id, :brand, :model, :year, :mileage, :fuel_type, 
                 :transmission, :location, :price, :currency, :description)
    ");

    $user_id = getCurrentUserId();
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':brand', $brand);
    $stmt->bindParam(':model', $model);
    $stmt->bindParam(':year', $year);
    $stmt->bindParam(':mileage', $mileage);
    $stmt->bindParam(':fuel_type', $fuelType);
    $stmt->bindParam(':transmission', $transmission);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':currency', $currency);
    $stmt->bindParam(':description', $description);

    if (!$stmt->execute()) {
        throw new Exception('Помилка під час додавання оголошення');
    }

    $car_id = $conn->lastInsertId();

    if (!empty($photos)) {
        $validPhotos = array_filter($photos, 'is_numeric');
        if (count($validPhotos) !== count($photos)) {
            throw new Exception('Некоректні ідентифікатори фотографій');
        }

        $placeholders = implode(',', array_map(function($key) { 
            return ':photo_id_' . $key; 
        }, array_keys($photos)));
        
        $sql = "UPDATE photos SET car_id = :car_id 
                WHERE id IN ({$placeholders})
                AND user_id = :user_id
                AND car_id IS NULL";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':car_id', $car_id);
        $stmt->bindParam(':user_id', $user_id);
        
        foreach ($photos as $key => $photoId) {
            $stmt->bindValue(':photo_id_' . $key, $photoId);
        }

        if (!$stmt->execute()) {
            throw new Exception('Помилка під час зв`язування фотографій');
        }

        if ($stmt->rowCount() !== count($photos)) {
            throw new Exception('Не всі фотографії були успішно пов`язані');
        }
    }

    $conn->commit();

    sendJsonResponse([
        'success' => true,
        'message' => 'Оголошення успішно додано',
        'car_id' => $car_id
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Add Listing Error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
} 