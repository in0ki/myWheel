<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    $query = "SELECT c.*, 
                     u.first_name, u.last_name,
                     (SELECT filename FROM photos WHERE car_id = c.id LIMIT 1) as main_photo
              FROM cars c
              LEFT JOIN users u ON c.user_id = u.id
              WHERE 1=1";
    $params = [];

    if (!empty($_GET['brand'])) {
        $query .= " AND c.brand = :brand";
        $params[':brand'] = $_GET['brand'];
    }
    
    if (!empty($_GET['model'])) {
        $query .= " AND c.model LIKE :model";
        $params[':model'] = "%" . $_GET['model'] . "%";
    }
    
    if (!empty($_GET['priceMin'])) {
        $query .= " AND c.price >= :priceMin";
        $params[':priceMin'] = $_GET['priceMin'];
    }
    
    if (!empty($_GET['priceMax'])) {
        $query .= " AND c.price <= :priceMax";
        $params[':priceMax'] = $_GET['priceMax'];
    }
    
    if (!empty($_GET['yearMin'])) {
        $query .= " AND c.year >= :yearMin";
        $params[':yearMin'] = $_GET['yearMin'];
    }
    
    if (!empty($_GET['yearMax'])) {
        $query .= " AND c.year <= :yearMax";
        $params[':yearMax'] = $_GET['yearMax'];
    }
    
    if (!empty($_GET['mileageMin'])) {
        $query .= " AND c.mileage >= :mileageMin";
        $params[':mileageMin'] = $_GET['mileageMin'];
    }
    
    if (!empty($_GET['mileageMax'])) {
        $query .= " AND c.mileage <= :mileageMax";
        $params[':mileageMax'] = $_GET['mileageMax'];
    }
    
    if (!empty($_GET['transmission'])) {
        $query .= " AND c.transmission = :transmission";
        $params[':transmission'] = $_GET['transmission'];
    }
    
    if (!empty($_GET['fuelType'])) {
        $query .= " AND c.fuel_type = :fuelType";
        $params[':fuelType'] = $_GET['fuelType'];
    }
    
    if (!empty($_GET['location'])) {
        $query .= " AND c.location = :location";
        $params[':location'] = $_GET['location'];
    }
    
    if (!empty($_GET['currency'])) {
        $query .= " AND c.currency = :currency";
        $params[':currency'] = $_GET['currency'];
    }
    
    $countQuery = preg_replace("/SELECT.*FROM/", "SELECT COUNT(*) FROM", $query);
    $stmt = $conn->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
 
    $itemsPerPage = 12;
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
   
    $query .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $itemsPerPage;
    $params[':offset'] = $offset;
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $listings = [];
    foreach ($result as $row) {
        $listings[] = [
            'id' => $row['id'],
            'title' => $row['brand'] . ' ' . $row['model'],
            'price' => $row['price'],
            'currency' => $row['currency'],
            'year' => $row['year'],
            'mileage' => $row['mileage'],
            'location' => $row['location'],
            'transmission' => $row['transmission'],
            'fuel_type' => $row['fuel_type'],
            'image_url' => $row['main_photo'] ? 'uploads/' . $row['main_photo'] : 'images/no-image.png',
            'seller_name' => $row['first_name'] . ' ' . $row['last_name'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'listings' => $listings,
        'pagination' => [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_listings.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Сталася помилка під час завантаження оголошень: ' . $e->getMessage()
    ]);
} 