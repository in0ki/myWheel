<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$conn = getDBConnection();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$where_conditions = ["1=1"];
$params = [];

if (!empty($_GET['brand'])) {
    $where_conditions[] = "brand = :brand";
    $params[':brand'] = $_GET['brand'];
}

if (!empty($_GET['model'])) {
    $where_conditions[] = "model LIKE :model";
    $params[':model'] = '%' . $_GET['model'] . '%';
}

if (!empty($_GET['priceMin'])) {
    $where_conditions[] = "price >= :price_min";
    $params[':price_min'] = $_GET['priceMin'];
}

if (!empty($_GET['priceMax'])) {
    $where_conditions[] = "price <= :price_max";
    $params[':price_max'] = $_GET['priceMax'];
}

if (!empty($_GET['yearMin'])) {
    $where_conditions[] = "year >= :year_min";
    $params[':year_min'] = $_GET['yearMin'];
}

if (!empty($_GET['yearMax'])) {
    $where_conditions[] = "year <= :year_max";
    $params[':year_max'] = $_GET['yearMax'];
}

if (!empty($_GET['transmission'])) {
    $where_conditions[] = "transmission = :transmission";
    $params[':transmission'] = $_GET['transmission'];
}

if (!empty($_GET['fuelType'])) {
    $where_conditions[] = "fuel_type = :fuel_type";
    $params[':fuel_type'] = $_GET['fuelType'];
}

if (!empty($_GET['location'])) {
    $where_conditions[] = "location = :location";
    $params[':location'] = $_GET['location'];
}

$where_clause = implode(" AND ", $where_conditions);

$count_query = "SELECT COUNT(*) as total FROM cars WHERE " . $where_clause;
$stmt = $conn->prepare($count_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_count / $per_page);

$query = "SELECT c.*, 
                 (SELECT filename FROM photos WHERE car_id = c.id ORDER BY id ASC LIMIT 1) as main_photo,
                 (SELECT COUNT(*) FROM favorites WHERE car_id = c.id) as favorites_count
          FROM cars c 
          WHERE " . $where_clause . "
          ORDER BY c.created_at DESC 
          LIMIT :offset, :per_page";

$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$brands = [];
$locations = [];

$stmt = $conn->query("SELECT DISTINCT brand FROM cars ORDER BY brand");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $brands[] = $row['brand'];
}

$stmt = $conn->query("SELECT DISTINCT location FROM cars ORDER BY location");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $locations[] = $row['location'];
}


?>

<div class="container-fluid mt-4">
    
    
    <div class="row">
        <div class="col-lg-12">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($listings as $listing): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo !empty($listing['main_photo']) ? 'uploads/' . $listing['main_photo'] : 'images/no-image.png'; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($listing['brand'] . ' ' . $listing['model']); ?>"
                                 style="height: 200px; object-fit: cover;">
                            
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($listing['brand'] . ' ' . $listing['model']); ?>
                                    (<?php echo $listing['year']; ?>)
                                </h5>
                                
                                <p class="card-text">
                                    <strong><?php echo number_format($listing['price']); ?> <?php echo $listing['currency']; ?></strong>
                                </p>
                                
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="fas fa-tachometer-alt"></i> <?php echo number_format($listing['mileage']); ?> км<br>
                                        <i class="fas fa-gas-pump"></i> <?php echo getFuelTypeText($listing['fuel_type']); ?><br>
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($listing['location']); ?>
                                    </small>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="car.php?id=<?php echo $listing['id']; ?>" class="btn btn-primary btn-sm">
                                        Детальніше
                                    </a>
                                    <small class="text-muted">
                                        <i class="far fa-heart"></i> <?php echo $listing['favorites_count']; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.css">
