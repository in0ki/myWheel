<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = getCurrentUserId();
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("
    SELECT c.*, 
           (SELECT filename FROM photos WHERE car_id = c.id ORDER BY is_main DESC LIMIT 1) as main_photo
    FROM cars c 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container mt-5 pt-4">
    <div class="row justify-content-center mb-5">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <div class="text-center mb-4">
                                <img src="<?php echo !empty($user['profile_photo']) ? 'uploads/profiles/' . $user['profile_photo'] : 'images/default-profile.png'; ?>" 
                                     class="rounded-circle mb-3" 
                                     alt="Фото профілю"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                
                                <h4 class="card-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        Email: <?php echo htmlspecialchars($user['email']); ?>
                                        <?php if (isset($user['show_email']) && $user['show_email']): ?>
                                            <span class="badge bg-success">Публічний</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Прихований</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                
                                <?php if (!empty($user['phone'])): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            Телефон: <?php echo htmlspecialchars($user['phone']); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="fas fa-edit"></i> Редагувати
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Мої оголошення</h5>
                    <!-- <a href="add_listing.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Додати оголошення
                    </a> -->
                </div>
                <div class="card-body">
                    <?php if (empty($listings)): ?>
                        <p class="text-center text-muted">У вас поки що немає оголошень</p>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                            <?php foreach ($listings as $listing): ?>
                                <div class="col-md-4 mb-4">
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
                                            <a href="car.php?id=<?php echo $listing['id']; ?>" class="btn btn-outline-primary">
                                                Детальніше
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-4 mb-4">
        <a href="logout.php" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Вийти з акаунта
        </a>
    </div>
</div>

<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редагувати профіль</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profilePhoto" class="form-label">Фото профілю</label>
                        <input type="file" class="form-control" id="profilePhoto" name="profile_photo" accept="image/jpeg,image/png">
                        <small class="text-muted">Підтримуються формати JPEG і PNG</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="firstName" class="form-label">Ім'я</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" 
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Прізвище</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" 
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="showEmail" name="show_email" 
                                   <?php echo isset($user['show_email']) && $user['show_email'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="showEmail">Показувати email публічно</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" onclick="updateProfile()">Зберегти</button>
            </div>
        </div>
    </div>
</div>

<script>
function updateProfile() {
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);
    
    fetch('api/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Сталася помилка під час оновлення профілю');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Сталася помилка під час оновлення профілю');
    });
}

function deleteListing(id) {
    if (confirm('Ви впевнені, що хочете видалити це оголошення?')) {
        fetch(`api/delete_listing.php?id=${id}`, {
            method: 'DELETE'
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