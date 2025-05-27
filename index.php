>

<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$conn = getDBConnection();

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

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>Знайдіть свій ідеальний автомобіль</h1>
                    <p class="lead">Найбільша база автомобілів у вашому регіоні. Купівля та продаж автомобілів стало простіше!</p>
                    <a href="catalog.php" class="btn btn-primary btn-lg">Почати пошук</a>
                </div>
                <div class="col-md-6">
                    <img src="images/hero-img.webp" alt="Hero Car" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <section class="featured-cars">
        <div class="container">
            <h2 class="text-center mb-4">Топ оголошення</h2>
            <div class="swiper featured-cars-swiper">
                <div class="swiper-wrapper">
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>

    <section class="all-listings">
        <div class="container">
            <h2 class="text-center mb-4">Всі оголошення</h2>
                <?php include 'includes/catalog.php'; ?>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2 class="text-center mb-5">Наші переваги</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-shield-alt fa-3x mb-3 text-primary"></i>
                        <h3>Безпека</h3>
                        <p>Перевірені продавці <br> та безпечні угоди</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-car-side fa-3x mb-3 text-primary"></i>
                        <h3>Великий вибір</h3>
                        <p>Тисячі автомобілів <br>на будь-який смак і бюджет</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-headset fa-3x mb-3 text-primary"></i>
                        <h3>Підтримка 24/7</h3>
                        <p>Завжди готові допомогти <br>з вибором автомобіля</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <button class="chat-button" data-bs-toggle="modal" data-bs-target="#chatModal">
        <i class="fas fa-comments"></i>
    </button>

    <div class="modal fade" id="chatModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Чат підтримки</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="chat-messages" id="chatMessages">
                    </div>
                    <div class="chat-input mt-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="messageInput" placeholder="Введіть повідомлення...">
                            <button class="btn btn-primary" id="sendMessage">Відправити</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/add-listing-modal.php'; ?>
    <?php include 'includes/footer.php'; ?>
