<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$listing_id = $_GET['id'] ?? null;

if (!$listing_id) {
    header('Location: my-listings.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT c.*, 
           (SELECT GROUP_CONCAT(filename) FROM photos WHERE car_id = c.id) as photos
    FROM cars c 
    WHERE c.id = ? AND c.user_id = ?
");
$stmt->execute([$listing_id, getCurrentUserId()]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    header('Location: my-listings.php');
    exit;
}

$photos = $listing['photos'] ? explode(',', $listing['photos']) : [];

include 'includes/header.php';
?>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">Редагування оголошення</h2>
                    
                    <form action="api/update_listing.php" method="POST" enctype="multipart/form-data" id="editListingForm">
                        <input type="hidden" name="id" value="<?php echo $listing_id; ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="brand" class="form-label">Марка *</label>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                       value="<?php echo htmlspecialchars($listing['brand']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="model" class="form-label">Модель *</label>
                                <input type="text" class="form-control" id="model" name="model" 
                                       value="<?php echo htmlspecialchars($listing['model']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="year" class="form-label">Рік *</label>
                                <input type="number" class="form-control" id="year" name="year" 
                                       value="<?php echo htmlspecialchars($listing['year']); ?>" 
                                       min="1900" max="<?php echo date('Y'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="mileage" class="form-label">Пробіг (км) *</label>
                                <input type="number" class="form-control" id="mileage" name="mileage" 
                                       value="<?php echo htmlspecialchars($listing['mileage']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fuel_type" class="form-label">Тип палива *</label>
                                <select class="form-select" id="fuel_type" name="fuel_type" required>
                                    <option value="petrol" <?php echo $listing['fuel_type'] === 'petrol' ? 'selected' : ''; ?>>Бензин</option>
                                    <option value="diesel" <?php echo $listing['fuel_type'] === 'diesel' ? 'selected' : ''; ?>>Дизель</option>
                                    <option value="hybrid" <?php echo $listing['fuel_type'] === 'hybrid' ? 'selected' : ''; ?>>Гібрид</option>
                                    <option value="electric" <?php echo $listing['fuel_type'] === 'electric' ? 'selected' : ''; ?>>Електро</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="transmission" class="form-label">Коробка передач *</label>
                                <select class="form-select" id="transmission" name="transmission" required>
                                    <option value="manual" <?php echo $listing['transmission'] === 'manual' ? 'selected' : ''; ?>>Механіка</option>
                                    <option value="automatic" <?php echo $listing['transmission'] === 'automatic' ? 'selected' : ''; ?>>Автомат</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Місцезнаходження *</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?php echo htmlspecialchars($listing['location']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Ціна *</label>
 
                            <div class="input-group">
                            <input type="number" class="form-control" id="price" name="price" 
                            value="<?php echo (int)round(floatval(str_replace(',', '.', $listing['price']))); ?>" required>
                                <select class="form-select" id="currency" name="currency" style="max-width: 120px;">
                                    <option value="UAH" <?php echo $listing['currency'] === 'UAH' ? 'selected' : ''; ?>>₴</option>
                                    <option value="USD" <?php echo $listing['currency'] === 'USD' ? 'selected' : ''; ?>>$</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Опис *</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($listing['description']); ?></textarea>
                        </div>

                        <?php if (!empty($photos)): ?>
                            <div class="mb-3">
                                <label class="form-label">Поточні фотографії</label>
                                <div class="row g-2">
                                    <?php foreach ($photos as $photo): ?>
                                        <div class="col-md-3">
                                            <div class="position-relative">
                                                <img src="uploads/<?php echo htmlspecialchars($photo); ?>" 
                                                     class="img-thumbnail" 
                                                     alt="Фото автомобиля">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                                        onclick="deletePhoto('<?php echo htmlspecialchars($photo); ?>')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="photos" class="form-label">Додати нові фотографії</label>
                            <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*">
                            <div class="form-text">Можна вибрати кілька фотографій</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="my-listings.php" class="btn btn-secondary">Скасувати</a>
                            <button type="submit" class="btn btn-primary">Зберегти зміни</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deletePhoto(filename) {
    if (confirm('Ви впевнені, що хочете видалити цю фотографію?')) {
        fetch('api/delete_photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                filename: filename,
                listing_id: <?php echo $listing_id; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Помилка при видаленні фотографії');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Помилка при видаленні фотографії');
        });
    }
}

document.getElementById('editListingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let priceInput = document.getElementById('price');
    priceInput.value = Math.round(parseFloat(priceInput.value.replace(',', '.')));
    if (price < 1000) {
        alert('Мінімальна ціна: 1,000 ₴');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('api/update_listing.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'my-listings.php';
        } else {
            alert(data.message || 'Помилка при оновленні оголошення');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Помилка при оновленні оголошення');
    });
});
</script>

<?php include 'includes/footer.php'; ?> 