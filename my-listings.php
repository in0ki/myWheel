<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();
$user_id = getCurrentUserId();
$stmt = $conn->prepare("
    SELECT c.*, 
           (SELECT filename FROM photos WHERE car_id = c.id LIMIT 1) as main_photo
    FROM cars c 
    WHERE c.user_id = :user_id 
    ORDER BY c.created_at DESC
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
?>


 


    <main class="container" style="margin-bottom: 20px;">
 

        <?php if (empty($listings)): ?>
            <div class="alert alert-info">
            У вас поки що немає оголошень. <a href="#" data-bs-toggle="modal" data-bs-target="#addListingModal">Додати перше оголошення</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($listings as $listing): ?>
                    <div class="col">
                        <div class="card h-100">
                            <?php if ($listing['main_photo']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($listing['main_photo']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($listing['brand'] . ' ' . $listing['model']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-car fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($listing['brand'] . ' ' . $listing['model']); ?>
                                </h5>
                                <p class="card-text">
                                    <strong>Рік:</strong> <?php echo htmlspecialchars($listing['year']); ?><br>
                                    <strong>Пробіг:</strong> <?php echo number_format($listing['mileage']); ?> км<br>
                                    <strong>Ціна:</strong> <?php echo number_format($listing['price']); ?> 
                                    <?php echo $listing['currency'] === 'USD' ? '$' : '₴'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="car.php?id=<?php echo $listing['id']; ?>" class="btn btn-outline-primary">
                                    Детальніше
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                onclick="editListing(<?php echo $listing['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteListing(<?php echo $listing['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                Додано: <?php echo date('d.m.Y', strtotime($listing['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>


    <script>
        function editListing(id) {
            window.location.href = 'edit-listing.php?id=' + id;
        }

        function deleteListing(id) {
            if (confirm('Ви впевнені, що хочете видалити це оголошення?')) {
                fetch('api/delete_listing.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Сталася помилка під час видалення оголошення');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Сталася помилка під час видалення оголошення');
                });
            }
        }
    </script>
     <?php include 'includes/footer.php'; ?>