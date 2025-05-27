<?php
session_start();

function formatPrice($price, $currency) {
    if ($currency === 'USD') {
        return '$' . number_format($price);
    } else {
        return number_format($price) . ' ₴';
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    if ($file['size'] > $maxSize) {
        return false;
    }

    return true;
}

function generateUniqueFilename($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// function formatPrice($price, $currency) {
//     return number_format($price, 0, ',', ' ') . ($currency === 'USD' ? '$' : '₴');
// }

function getPriceIndicatorClass($price, $marketPrice) {
    if (!$marketPrice) return 'fair';
    
    $difference = (($price - $marketPrice) / $marketPrice) * 100;
    if ($difference <= -10) return 'favorable';
    if ($difference >= 10) return 'overpriced';
    return 'fair';
}

function getPriceIndicatorText($price, $marketPrice) {
    $class = getPriceIndicatorClass($price, $marketPrice);
    switch ($class) {
        case 'favorable':
            return 'Вигідна';
        case 'overpriced':
            return 'Завищена';
        default:
            return 'Чесна';
    }
}

function getTransmissionText($transmission) {
    return $transmission === 'manual' ? 'Механіка' : 'Автомат';
}

function getFuelTypeText($fuelType) {
    switch ($fuelType) {
        case 'petrol':
            return 'Бензин';
        case 'diesel':
            return 'Дизель';
        case 'hybrid':
            return 'Гібрид';
        case 'electric':
            return 'Електро';
        default:
            return $fuelType;
    }
} 