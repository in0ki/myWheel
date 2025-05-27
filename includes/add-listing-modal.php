    <div class="modal fade" id="addListingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Додати оголошення</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                            <button type="submit" class="btn btn-primary" id="submitButton" disabled>Опублікувати</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Вхід / Реєстрація</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="authTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#login">Вхід</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#register">Реєстрація</a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="login">
                            <form id="loginForm">
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="loginEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="loginPassword" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Ввійти</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="register">
                            <form id="registerForm">
                                <div class="mb-3">
                                    <label for="firstName" class="form-label">Ім'я</label>
                                    <input type="text" class="form-control" id="firstName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="lastName" class="form-label">Прізвище</label>
                                    <input type="text" class="form-control" id="lastName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="registerEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="registerEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="registerPassword" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="registerPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Підтвердження пароля</label>
                                    <input type="password" class="form-control" id="confirmPassword" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Зареєструватися</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>