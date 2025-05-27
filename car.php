<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$car_id = $_GET['id'] ?? null;

if (!$car_id) {
    header('Location: catalog.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT c.*, u.first_name, u.last_name, u.phone, u.show_email, u.email,
           (SELECT COUNT(*) FROM views WHERE car_id = c.id) as views_count
    FROM cars c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header('Location: catalog.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM cars WHERE user_id = u.id) as total_listings
    FROM users u 
    WHERE u.id = ?
");
$stmt->execute([$car['user_id']]);
$seller = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM photos WHERE car_id = ? ORDER BY is_main DESC");
$stmt->execute([$car_id]);
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("
    SELECT c.*, 
           (SELECT filename FROM photos WHERE car_id = c.id LIMIT 1) as main_photo
    FROM cars c 
    WHERE c.id != ? 
    AND (c.brand = ? OR c.fuel_type = ? OR c.transmission = ?)
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$car_id, $car['brand'], $car['fuel_type'], $car['transmission']]);
$similar_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("INSERT INTO views (car_id, viewed_at) VALUES (?, NOW())");
$stmt->execute([$car_id]);

include 'includes/header.php';
?>

<div class="container mt-5 pt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="mb-4">
                        <div id="carGallery" class="carousel slide">
                            <?php if (count($photos) > 1): ?>
                                <div class="carousel-indicators">
                                    <?php foreach ($photos as $index => $photo): ?>
                                        <button type="button" data-bs-target="#carGallery" data-bs-slide-to="<?php echo $index; ?>" 
                                                <?php echo $index === 0 ? 'class="active"' : ''; ?>></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="carousel-inner">
    <?php if (empty($photos)): ?>
        <div class="carousel-item active">
            <img src="images/no-image.png" 
                 class="d-block w-100" 
                 alt="Зображення відсутнє"
                 style="height: 400px; object-fit: contain; background-color: #f8f9fa;">
        </div>
    <?php else: ?>
        <?php foreach ($photos as $index => $photo): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="uploads/<?php echo htmlspecialchars($photo['filename']); ?>" 
                     class="d-block w-100" 
                     alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>"
                     style="height: 400px; object-fit: contain; background-color: #f8f9fa;">
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div> 
                            <?php if (count($photos) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carGallery" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carGallery" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="card-title mb-1">
                                <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>
                            </h2>
                            <p class="text-muted mb-0">
                                <small>Переглядів: <?php echo number_format($car['views_count']); ?></small>
                            </p>
                        </div>
                        <div class="text-end">
                            <h3 class="text-primary mb-0">
                                <?php echo number_format($car['price'], 0, ',', ' '); ?> <?php echo $car['currency']; ?>
                            </h3>
                            <span class="badge bg-success">Чесна ціна</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-road me-2"></i>
                                <span><?php echo number_format($car['mileage'], 0, ',', ' '); ?> км</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-gas-pump me-2"></i>
                                <span><?php echo getFuelTypeText($car['fuel_type']); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-cog me-2"></i>
                                <span><?php echo getTransmissionText($car['transmission']); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <span><?php echo htmlspecialchars($car['location']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Опис</h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Інформація про продавця</h5>
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo !empty($seller['profile_photo']) ? 'uploads/profiles/' . $seller['profile_photo'] : 'images/default-profile.png'; ?>" 
                             class="rounded-circle me-3" 
                             alt="Фото продавця"
                             style="width: 50px; height: 50px; object-fit: cover;">
                        <div>
                            <a href="user-profile.php?id=<?php echo $car['user_id']; ?>" class="text-decoration-none">
                                <h5 class="mb-0"><?php echo htmlspecialchars($car['first_name'] . ' ' . $car['last_name']); ?></h5>
                            </a>
                            <small class="text-muted">
                                <i class="fas fa-car"></i> <?php echo $seller['total_listings']; ?> оголошень
                            </small>
                        </div>
                    </div>
                    
                    <div class="contact-info">
                        <?php if ($car['show_email']): ?>
                            <p class="mb-2">
                                <i class="fas fa-envelope"></i> 
                                <?php echo htmlspecialchars($car['email']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($car['phone'])): ?>
                            <p class="mb-2">
                                <i class="fas fa-phone"></i> 
                                <?php echo htmlspecialchars($car['phone']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <p class="text-muted mb-0">
                        <small>Оголошення створено <?php echo date('d.m.Y', strtotime($car['created_at'])); ?></small>
                    </p>
                    <?php if (isLoggedIn()): ?>
                        <button class="btn btn-outline-primary w-100 mt-3" onclick="addToFavorites(<?php echo $car_id; ?>)">
                            <i class="fas fa-heart me-2"></i>Додати до обраного
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($similar_cars)): ?>
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4">Схожі оголошення</h4>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <?php foreach ($similar_cars as $similar): ?>

                        <div class="col">
                            <div class="card h-100">
                                <div class="position-relative">
                                    <img src="<?php echo !empty($similar['main_photo']) ? 'uploads/' . $similar['main_photo'] : 'images/no-image.png'; ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($similar['brand'] . ' ' . $similar['model']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                    <span class="position-absolute top-0 end-0 badge bg-primary m-2">
                                        <?php echo number_format($similar['price'], 0, ',', ' '); ?> <?php echo $similar['currency']; ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($similar['brand'] . ' ' . $similar['model']); ?></h5>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <?php echo $similar['year']; ?> г. • 
                                            <?php echo number_format($similar['mileage'], 0, ',', ' '); ?> км<br>
                                            <?php echo $similar['location']; ?>
                                        </small>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="car.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline-primary w-100">
                                        Детальніше
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function openChat(userId) {
    alert('Функція чату буде доступна найближчим часом');
}

function addToFavorites(carId) {
    fetch('api/add_to_favorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ car_id: carId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Оголошення додано до обраного');
        } else {
            alert(data.message || 'Помилка при додаванні до обраного');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при додаванні до обраного');
    });
}
</script>

<?php include 'includes/footer.php'; ?> 