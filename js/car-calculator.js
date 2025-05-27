document.addEventListener('DOMContentLoaded', function() {
    const calculatorForm = document.getElementById('calculatorForm');
    const resultDiv = document.getElementById('calculatorResult');

    const basePrices = {
        'BMW': 35000,
        'Mercedes-Benz': 38000,
        'Audi': 33000,
        'Toyota': 25000,
        'Honda': 23000,
        'Volkswagen': 27000,
        'Ford': 24000,
        'Hyundai': 22000,
        'Kia': 21000,
        'Mazda': 24000,
        'Nissan': 23000,
        'Lexus': 40000,
        'Porsche': 65000,
        'Volvo': 35000,
        'Subaru': 26000,
        // Default price for other brands
        'default': 25000
    };

    const coefficients = {
        age: {
            new: 1,
            '1-3': 0.85,
            '4-7': 0.7,
            '8-12': 0.5,
            '13+': 0.35
        },
        mileage: {
            low: 1,      // 0-50,000 km
            medium: 0.85, // 50,001-100,000 km
            high: 0.7,   // 100,001-150,000 km
            very_high: 0.5 // 150,000+ km
        },
        condition: {
            excellent: 1,
            good: 0.9,
            fair: 0.75,
            poor: 0.6
        },
        fuel: {
            electric: 1.2,
            hybrid: 1.1,
            petrol: 1,
            diesel: 0.95
        },
        transmission: {
            automatic: 1.1,
            robot: 1.05,
            variator: 1,
            manual: 0.95
        }
    };

    calculatorForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const brand = document.getElementById('calcBrand').value;
        const year = parseInt(document.getElementById('calcYear').value);
        const mileage = parseInt(document.getElementById('calcMileage').value);
        const condition = document.getElementById('calcCondition').value;
        const fuel = document.getElementById('calcFuel').value;
        const transmission = document.getElementById('calcTransmission').value;

        const basePrice = basePrices[brand] || basePrices.default;

        const age = new Date().getFullYear() - year;
        let ageCoef;
        if (age === 0) ageCoef = coefficients.age.new;
        else if (age <= 3) ageCoef = coefficients.age['1-3'];
        else if (age <= 7) ageCoef = coefficients.age['4-7'];
        else if (age <= 12) ageCoef = coefficients.age['8-12'];
        else ageCoef = coefficients.age['13+'];

        let mileageCoef;
        if (mileage <= 50000) mileageCoef = coefficients.mileage.low;
        else if (mileage <= 100000) mileageCoef = coefficients.mileage.medium;
        else if (mileage <= 150000) mileageCoef = coefficients.mileage.high;
        else mileageCoef = coefficients.mileage.very_high;

        const conditionCoef = coefficients.condition[condition];
        const fuelCoef = coefficients.fuel[fuel];
        const transmissionCoef = coefficients.transmission[transmission];


        const finalPrice = Math.round(basePrice * ageCoef * mileageCoef * conditionCoef * fuelCoef * transmissionCoef);
     
        const uahPrice = Math.round(finalPrice * 37);

        resultDiv.innerHTML = `
            <div class="calculator-result">
                <h4>Розрахована вартість автомобіля</h4>
                
                <div class="price-block">
                    <div class="currency">USD</div>
                    <div class="amount">$${finalPrice.toLocaleString()}</div>
                </div>
                
                <div class="price-block">
                    <div class="currency">UAH</div>
                    <div class="amount">₴${uahPrice.toLocaleString()}</div>
                </div>

                <div class="price-factors">
                    <div class="factor">
                        <span class="factor-name">Базова вартість (${brand})</span>
                        <span class="factor-value">$${basePrice.toLocaleString()}</span>
                    </div>
                    <div class="factor">
                        <span class="factor-name">Вік автомобіля (${age} років)</span>
                        <span class="factor-value">${Math.round(ageCoef * 100)}%</span>
                    </div>
                    <div class="factor">
                        <span class="factor-name">Пробіг (${mileage.toLocaleString()} км)</span>
                        <span class="factor-value">${Math.round(mileageCoef * 100)}%</span>
                    </div>
                    <div class="factor">
                        <span class="factor-name">Стан</span>
                        <span class="factor-value">${Math.round(conditionCoef * 100)}%</span>
                    </div>
                    <div class="factor">
                        <span class="factor-name">Тип палива</span>
                        <span class="factor-value">${Math.round(fuelCoef * 100)}%</span>
                    </div>
                    <div class="factor">
                        <span class="factor-name">Тип КПП</span>
                        <span class="factor-value">${Math.round(transmissionCoef * 100)}%</span>
                    </div>
                </div>

                <div class="calculator-note">
                    * Розрахунок є приблизним та базується на середньоринкових цінах
                </div>
            </div>
        `;
    });
}); 