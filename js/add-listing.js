document.addEventListener('DOMContentLoaded', function() {
    const yearSelect = document.getElementById('year');
    const currentYear = new Date().getFullYear();
    for (let year = currentYear; year >= 1950; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    }

    const locationSelect = document.getElementById('location');
    const cities = [
        'Київ',
        'Харків',
        'Одеса',
        'Дніпро',
        'Львів',
        'Запоріжжя',
        'Кривий Ріг',
        'Миколаїв',
        'Вінниця',
        'Чернігів'
    ];
    cities.forEach(city => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        locationSelect.appendChild(option);
    });

    const brandSelect = document.getElementById('brand');
    const brands = [
        'Toyota',
        'Volkswagen',
        'BMW',
        'Mercedes-Benz',
        'Audi',
        'Hyundai',
        'Kia',
        'Nissan',
        'Skoda',
        'Renault',
        'Ford',
        'Honda',
        'Mazda',
        'Lexus',
        'Chevrolet',
        'Citroen',
        'Peugeot',
        'Opel',
        'Mitsubishi',
        'Volvo'
    ];
    brands.forEach(brand => {
        const option = document.createElement('option');
        option.value = brand;
        option.textContent = brand;
        brandSelect.appendChild(option);
    });

    const form = document.getElementById('addListingForm');
    const submitButton = document.getElementById('submitButton');
    const requiredFields = form.querySelectorAll('[required]');

    function validateForm() {
        let isValid = true;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
            }
        });

        const mileage = document.getElementById('mileage');
        if (mileage.value < 0) {
            isValid = false;
            mileage.classList.add('is-invalid');
        } else {
            mileage.classList.remove('is-invalid');
        }

        const price = document.getElementById('price');
        if (price.value < 1000) {
            isValid = false;
            price.classList.add('is-invalid');
        } else {
            price.classList.remove('is-invalid');
        }

        const description = document.getElementById('description');
        if (description.value.length > 1000) {
            isValid = false;
            description.classList.add('is-invalid');
        } else {
            description.classList.remove('is-invalid');
        }

        submitButton.disabled = !isValid;
    }

    requiredFields.forEach(field => {
        field.addEventListener('input', validateForm);
        field.addEventListener('change', validateForm);
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
 
        const dropzone = Dropzone.forElement("#photoDropzone");
        const uploadedFiles = dropzone.getAcceptedFiles();
        const fileIds = uploadedFiles.map(file => file.serverId);
        formData.append('photos', JSON.stringify(fileIds));

        fetch('api/add_listing.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const data = JSON.parse(text);
                        throw new Error(data.message || 'Помилка сервера');
                    } catch (e) {
                        if (e instanceof SyntaxError) {
                            throw new Error('Помилка під час обробки відповіді сервера: ' + text);
                        }
                        throw e;
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = 'my-listings.php';
            } else {
                alert(data.message || 'Сталася помилка під час публікації оголошення');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Сталася помилка під час надсилання форми');
        });
    });
}); 