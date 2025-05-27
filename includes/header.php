<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyWheels - Продаж і купівля автомобілів</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <link href="css/style.css" rel="stylesheet">
    <link href="css/catalog.css" rel="stylesheet">
    <link rel="stylesheet" href="css/calculator.css">
</head>
<body>
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="images/logo.png" alt="Logo" height="40">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="catalog.php">Каталог</a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="favorites.php">Обране</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="calculator.php">Калькулятор ціни</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary me-2" href="#" data-bs-toggle="modal" data-bs-target="#addListingModal" style="color: white;">Додати оголошення</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <?php if (isLoggedIn()): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-2"></i>
                                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Пользователь'); ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Мій профіль</a></li>
                                    <li><a class="dropdown-item" href="my-listings.php"><i class="fas fa-list me-2"></i>Мої оголошення</a></li>
                                    <!-- <li><a class="dropdown-item" href="favorites.php"><i class="fas fa-heart me-2"></i>Обране</a></li> -->
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Вийти</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                                Вхід / Реєстрація
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <?php include 'includes/add-listing-modal.php'; ?>