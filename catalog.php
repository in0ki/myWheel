<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$conn = getDBConnection();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
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

if (!empty($_GET['mileageMin'])) {
    $where_conditions[] = "mileage >= :mileage_min";
    $params[':mileage_min'] = $_GET['mileageMin'];
}
if (!empty($_GET['mileageMax'])) {
    $where_conditions[] = "mileage <= :mileage_max";
    $params[':mileage_max'] = $_GET['mileageMax'];
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

if (!empty($_GET['currency'])) {
    $where_conditions[] = "currency = :currency";
    $params[':currency'] = $_GET['currency'];
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

include 'includes/header.php';
?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">Каталог автомобілів</h1>
    
    <div class="row">
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Фільтри</h5>
                    <form method="GET" action="catalog.php">
                        <div class="mb-3">
                            <label for="brand" class="form-label">Марка</label>
                            <select class="form-select" id="brand" name="brand">
                                <option value="">Всі марки</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo htmlspecialchars($brand); ?>" 
                                            <?php echo isset($_GET['brand']) && $_GET['brand'] === $brand ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Модель</label>
                            <input type="text" class="form-control" id="model" name="model" 
                                   value="<?php echo isset($_GET['model']) ? htmlspecialchars($_GET['model']) : ''; ?>"
                                   placeholder="Введіть модель">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Рік випуску</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" name="yearMin" 
                                           value="<?php echo isset($_GET['yearMin']) ? htmlspecialchars($_GET['yearMin']) : ''; ?>"
                                           placeholder="Від" min="1950" max="<?php echo date('Y'); ?>">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="yearMax" 
                                           value="<?php echo isset($_GET['yearMax']) ? htmlspecialchars($_GET['yearMax']) : ''; ?>"
                                           placeholder="До" min="1950" max="<?php echo date('Y'); ?>">
                                </div>
                            </div>
                        </div>

                    <div class="mb-3">
                        <label class="form-label">Ціна</label>
                        <div class="mb-2">
                            <select class="form-select" name="currency">
                                
                                <option value="USD" <?php echo isset($_GET['currency']) && $_GET['currency'] === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                <option value="UAH" <?php echo isset($_GET['currency']) && $_GET['currency'] === 'UAH' ? 'selected' : ''; ?>>UAH (₴)</option>
                            </select>
                        </div>
                        <div class="row mt-2">
                            <div class="col">
                                <input type="number" class="form-control" name="priceMin" 
                                    value="<?php echo isset($_GET['priceMin']) ? htmlspecialchars($_GET['priceMin']) : ''; ?>"
                                    placeholder="Від">
                            </div>
                            <div class="col">
                                <input type="number" class="form-control" name="priceMax" 
                                    value="<?php echo isset($_GET['priceMax']) ? htmlspecialchars($_GET['priceMax']) : ''; ?>"
                                    placeholder="До">
                            </div>
                        </div>
                    </div>

                        <div class="mb-3">
                            <label class="form-label">Пробіг</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" name="mileageMin" 
                                           value="<?php echo isset($_GET['mileageMin']) ? htmlspecialchars($_GET['mileageMin']) : ''; ?>"
                                           placeholder="Від">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="mileageMax" 
                                           value="<?php echo isset($_GET['mileageMax']) ? htmlspecialchars($_GET['mileageMax']) : ''; ?>"
                                           placeholder="До">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="transmission" class="form-label">Коробка передач</label>
                            <select class="form-select" id="transmission" name="transmission">
                                <option value="">Будь-яка</option>
                                <option value="manual" <?php echo isset($_GET['transmission']) && $_GET['transmission'] === 'manual' ? 'selected' : ''; ?>>Механіка</option>
                                <option value="automatic" <?php echo isset($_GET['transmission']) && $_GET['transmission'] === 'automatic' ? 'selected' : ''; ?>>Автомат</option>
                                <option value="robot" <?php echo isset($_GET['transmission']) && $_GET['transmission'] === 'robot' ? 'selected' : ''; ?>>Робот</option>
                                <option value="variator" <?php echo isset($_GET['transmission']) && $_GET['transmission'] === 'variator' ? 'selected' : ''; ?>>Варіатор</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fuelType" class="form-label">Тип палива</label>
                            <select class="form-select" id="fuelType" name="fuelType">
                                <option value="">Будь-який</option>
                                <option value="petrol" <?php echo isset($_GET['fuelType']) && $_GET['fuelType'] === 'petrol' ? 'selected' : ''; ?>>Бензин</option>
                                <option value="diesel" <?php echo isset($_GET['fuelType']) && $_GET['fuelType'] === 'diesel' ? 'selected' : ''; ?>>Дизель</option>
                                <option value="hybrid" <?php echo isset($_GET['fuelType']) && $_GET['fuelType'] === 'hybrid' ? 'selected' : ''; ?>>Гібрид</option>
                                <option value="electric" <?php echo isset($_GET['fuelType']) && $_GET['fuelType'] === 'electric' ? 'selected' : ''; ?>>Електро</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Місцезнаходження</label>
                            <select class="form-select" id="location" name="location">
                                <option value="">Всі міста</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo htmlspecialchars($location); ?>" 
                                            <?php echo isset($_GET['location']) && $_GET['location'] === $location ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($location); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Застосувати фільтри</button>
                            <a href="catalog.php" class="btn btn-secondary">Скинути</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="mb-4">
                <h5>Знайдено оголошень: <?php echo $total_count; ?></h5>
            </div>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php if (empty($listings)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            Оголошення не знайдені
                        </div>
                    </div>
                <?php else: ?>
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
                <?php endif; ?>
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

<?php include 'includes/footer.php'; ?>