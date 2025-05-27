<?php
require_once 'api/config.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додати оголошення - АвтоПродажа</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
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
                            <a class="nav-link" href="catalog.php">Каталог</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="favorites.php">Обране</a>
                        </li>
                        <li class="nav-item user-menu" style="display: none;">
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="user-name">Ім'я користувача</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                                    <li><a class="dropdown-item" href="profile.php">Мій профіль</a></li>
                                    <li><a class="dropdown-item" href="my-listings.php">Мої оголошення</a></li>
                                    <li><a class="dropdown-item" href="favorites.php">Обране</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" id="logoutButton">Вийти</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Додати оголошення</h2>
                    </div>
                    <div class="card-body">
                        <form id="addListingForm" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label">Фотографії автомобіля (до 10 шт.)</label>
                                <div class="custom-dropzone" id="photoDropzone" data-url="api/upload_photo.php">
                                    <div class="dz-message">
                                        Перетягніть фотографії сюди або натисніть для вибору
                                    </div>
                                </div>
                                <div class="form-text">Підтримувані формати: JPEG, PNG. Максимальний розмір: 5MB</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="brand" class="form-label">Марка автомобіля *</label>
                                    <select class="form-select" id="brand" name="brand" required>
                                        <option value="">Виберіть марку</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="model" class="form-label">Модель *</label>
                                    <input type="text" class="form-control" id="model" name="model" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="year" class="form-label">Рік випуску *</label>
                                    <select class="form-select" id="year" name="year" required>
                                        <option value="">Виберіть рік</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="mileage" class="form-label">Пробіг (км) *</label>
                                    <input type="number" class="form-control" id="mileage" name="mileage" min="0" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="fuelType" class="form-label">Тип палива *</label>
                                    <select class="form-select" id="fuelType" name="fuelType" required>
                                        <option value="">Виберіть тип палива</option>
                                        <option value="petrol">Бензин</option>
                                        <option value="diesel">Дизель</option>
                                        <option value="hybrid">Гібрид</option>
                                        <option value="electric">Електро</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="transmission" class="form-label">Коробка передач *</label>
                                    <select class="form-select" id="transmission" name="transmission" required>
                                        <option value="">Виберіть тип КПП</option>
                                        <option value="manual">Механіка</option>
                                        <option value="automatic">Автомат</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Місцезнаходження *</label>
                                <select class="form-select" id="location" name="location" required>
                                    <option value="">Виберіть місто</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Ціна *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="price" name="price" min="1000" required>
                                    <select class="form-select" id="currency" name="currency" style="max-width: 120px;">
                                        <option value="UAH">₴</option>
                                        <option value="USD">$</option>
                                    </select>
                                </div>
                                <div class="form-text">Мінімальна ціна: 1,000 ₴</div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label">Опис *</label>
                                <textarea class="form-control" id="description" name="description" rows="5" maxlength="1000" required></textarea>
                                <div class="form-text">Максимум 1000 символів</div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" onclick="history.back()">Скасувати</button>
                                <button type="submit" class="btn btn-primary" id="submitButton" disabled>Опублікувати</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <script src="js/main.js"></script>
    <script src="js/add-listing.js"></script>
</body>
</html> 