<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$conn = getDBConnection();

$brands = [];
$stmt = $conn->query("SELECT DISTINCT brand FROM cars ORDER BY brand");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $brands[] = $row['brand'];
}

include 'includes/header.php';
?>

<div class="container" style="margin-top: 140px;">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Калькулятор вартості автомобіля</h3>
                </div>
                <div class="card-body">
                    <form id="calculatorForm">
                        <div class="mb-3">
                            <label for="calcBrand" class="form-label">Марка автомобіля</label>
                            <select class="form-select" id="calcBrand" required>
                                <option value="">Оберіть марку</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo htmlspecialchars($brand); ?>">
                                        <?php echo htmlspecialchars($brand); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="calcYear" class="form-label">Рік випуску</label>
                            <select class="form-select" id="calcYear" required>
                                <option value="">Оберіть рік</option>
                                <?php 
                                $currentYear = date('Y');
                                for ($year = $currentYear; $year >= 1990; $year--) {
                                    echo "<option value=\"$year\">$year</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="calcMileage" class="form-label">Пробіг (км)</label>
                            <input type="number" class="form-control" id="calcMileage" min="0" step="1000" required>
                        </div>

                        <div class="mb-3">
                            <label for="calcCondition" class="form-label">Стан автомобіля</label>
                            <select class="form-select" id="calcCondition" required>
                                <option value="">Оберіть стан</option>
                                <option value="excellent">Відмінний</option>
                                <option value="good">Добрий</option>
                                <option value="fair">Задовільний</option>
                                <option value="poor">Поганий</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="calcFuel" class="form-label">Тип палива</label>
                            <select class="form-select" id="calcFuel" required>
                                <option value="">Оберіть тип палива</option>
                                <option value="petrol">Бензин</option>
                                <option value="diesel">Дизель</option>
                                <option value="hybrid">Гібрид</option>
                                <option value="electric">Електро</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="calcTransmission" class="form-label">Коробка передач</label>
                            <select class="form-select" id="calcTransmission" required>
                                <option value="">Оберіть тип КПП</option>
                                <option value="manual">Механічна</option>
                                <option value="automatic">Автоматична</option>
                                <option value="robot">Роботизована</option>
                                <option value="variator">Варіатор</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Розрахувати вартість</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div id="calculatorResult">
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">Як працює калькулятор?</h4>
                </div>
                <div class="card-body">
                    <p>Калькулятор враховує наступні фактори при розрахунку вартості:</p>
                    <ul>
                        <li>Базова вартість моделі на ринку</li>
                        <li>Рік випуску та вік автомобіля</li>
                        <li>Пробіг та його вплив на знос</li>
                        <li>Загальний технічний стан</li>
                        <li>Тип палива та його актуальність</li>
                        <li>Тип коробки передач</li>
                    </ul>
                    <p class="mb-0">
                        <small class="text-muted">
                            Результат розрахунку є приблизним і може відрізнятися від реальної ринкової вартості 
                            залежно від багатьох додаткових факторів.
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/car-calculator.js"></script> 