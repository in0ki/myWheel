<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT c.*, u.first_name, u.last_name,
           (SELECT filename FROM photos WHERE car_id = c.id LIMIT 1) as main_photo
    FROM favorites f
    JOIN cars c ON f.car_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE f.user_id = ?
");
$stmt->execute([getCurrentUserId()]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';


?>

<main class="container mt-5 pt-4">
    <h1 class="mb-4">Обране</h1>
    
   
    

    
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4" id="favoritesGrid">
        <?php if (empty($favorites)): ?>
            <div class="col-12 text-center">
                <p>Список порожній. Ви ще не додали жодного оголошення в обране.</p>
            </div>
        <?php else: ?>
            <?php foreach ($favorites as $favorite): ?>
                <!-- <?php  print_r($favorite); ?> -->
                <div class="col">
                    <div class="card h-100">
                        <div class="position-relative">
                            <img src="<?php echo !empty($favorite['main_photo']) ? 'uploads/' . $favorite['main_photo'] : 'images/no-image.png'; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($favorite['brand'] . ' ' . $favorite['model']); ?>"
                                 style="height: 200px; object-fit: cover;">
                            <span class="position-absolute top-0 end-0 badge bg-primary m-2">
                                <?php echo number_format($favorite['price'], 0, ',', ' '); ?> <?php echo $favorite['currency']; ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($favorite['brand'] . ' ' . $favorite['model']); ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    <?php echo $favorite['year']; ?> г. • 
                                    <?php echo number_format($favorite['mileage'], 0, ',', ' '); ?> км<br>
                                    <?php echo $favorite['location']; ?>
                                </small>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="car.php?id=<?php echo $favorite['id']; ?>" class="btn btn-outline-primary w-100">
                                Детальніше
                            </a>
                            <button class="btn btn-outline-danger w-100 mt-2" onclick="removeFromFavorites(<?php echo $favorite['id']; ?>)">
                                <i class="fas fa-trash me-2"></i>Видалити з обраного
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
function removeFromFavorites(carId) {
    fetch('api/remove_from_favorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ car_id: carId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`[data-car-id="${carId}"]`);
            if (card) {
                card.remove();
            }
            alert('Оголошення видалено з обраного');
        } else {
            alert(data.message || 'Помилка під час видалення з обраного');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка під час видалення з обраного');
    });
}
</script>

<?php include 'includes/footer.php'; ?> 