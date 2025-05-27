<?php
// session_start();
require_once 'includes/functions.php';
// require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/header.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT u.*, 
               COUNT(DISTINCT c.id) as total_listings,
               (SELECT COUNT(*) FROM favorites f 
                JOIN cars c2 ON f.car_id = c2.id 
                WHERE c2.user_id = u.id) as total_favorites
        FROM users u 
        LEFT JOIN cars c ON u.id = c.user_id 
        WHERE u.id = :user_id 
        GROUP BY u.id
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Користувач не знайдений');
    }

    $query = "SELECT c.*, 
               (SELECT filename FROM photos WHERE car_id = c.id ORDER BY id ASC LIMIT 1) as main_photo,
               (SELECT COUNT(*) FROM favorites WHERE car_id = c.id) as favorites_count
        FROM cars c 
        WHERE c.user_id = :user_id
        ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4 mb-4 mt-5">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <img src="<?php echo !empty($user['profile_photo']) ? 'uploads/profiles/' . $user['profile_photo'] : 'images/default-profile.png'; ?>" 
                             class="rounded-circle mb-3" 
                             alt="Фото профілю"
                             style="width: 150px; height: 150px; object-fit: cover;">
                        
                        <h4 class="card-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                        
                        <div class="mb-3">
                            <?php if ($user['show_email']): ?>
                                <p class="mb-2">
                                    <i class="fas fa-envelope"></i> 
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($user['phone'])): ?>
                                <p class="mb-2">
                                    <i class="fas fa-phone"></i> 
                                    <?php echo htmlspecialchars($user['phone']); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-around text-center mt-4">
                            <div>
                                <h5><?php echo $user['total_listings']; ?></h5>
                                <small class="text-muted">Оголошень</small>
                            </div>
                            <div>
                                <h5><?php echo $user['total_favorites']; ?></h5>
                                <small class="text-muted">В обраному</small>
                            </div>
                        </div>
                        
                        <p class="text-muted mt-3">
                            <small>На сайті з <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></small>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h4 class="mb-4">Оголошення користувача</h4>
                
                <?php if (empty($listings)): ?>
                    <div class="alert alert-info">
                        У користувача поки що немає активних оголошень.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($listings as $listing): ?>
                            <div class="col-md-6 mb-4">
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
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
} catch (Exception $e) {
    ?>
    <div class="container mt-5">
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($e->getMessage()); ?>
        </div>
    </div>
    <?php
}

require_once 'includes/footer.php';
?>