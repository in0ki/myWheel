document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });

    Dropzone.autoDiscover = false;
    let dropzone;

    function initializeAddListingForm() {
        if (!dropzone) {
            const dropzoneElement = document.getElementById('photoDropzone');
            if (dropzoneElement) {
                dropzoneElement.classList.add('dropzone');
                dropzone = new Dropzone("#photoDropzone", {
                    url: dropzoneElement.dataset.url || "api/upload_photo.php",
                    maxFiles: 10,
                    maxFilesize: 5, // MB
                    acceptedFiles: "image/jpeg,image/png",
                    addRemoveLinks: true,
                    dictDefaultMessage: "Перетягніть фотографії сюди або натисніть для вибору",
                    dictRemoveFile: "Видалити",
                    dictFileTooBig: "Файл занадто великий ({{filesize}}MB). Максимальний розмір: {{maxFilesize}}MB.",
                    dictInvalidFileType: "Можна завантажувати тільки зображення (JPEG, PNG)",
                    dictCancelUpload: "Скасувати",
                    dictUploadCanceled: "Завантаження скасовано",
                    dictCancelUploadConfirmation: "Ви впевнені, що хочете скасувати завантаження?",
                    dictMaxFilesExceeded: "Ви не можете завантажити більше файлів",
                    init: function() {
                        this.on("success", function(file, response) {
                            console.log('Raw response:', response);
                            try {
                                const data = typeof response === 'string' ? JSON.parse(response) : response;
                                if (data.success) {
                                    file.serverId = data.photo_id;
                                    console.log('Photo uploaded successfully:', data);
                                } else {
                                    this.removeFile(file);
                                    alert(data.message || 'Помилка під час завантаження файлу');
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                                console.error('Response:', response);
                                this.removeFile(file);
                                alert('Помилка під час обробки відповіді сервера: ' + e.message);
                            }
                        });

                        this.on("error", function(file, errorMessage, xhr) {
                            console.error('Upload error:', errorMessage);
                            if (xhr) {
                                console.error('XHR status:', xhr.status);
                                console.error('XHR response:', xhr.responseText);
                            }
                            this.removeFile(file);
                            alert(typeof errorMessage === 'string' ? errorMessage : 'Помилка під час завантаження файлу');
                        });
                    }
                });
            }
        }

        const yearSelect = document.getElementById('year');
        if (yearSelect && yearSelect.options.length <= 1) {
            const currentYear = new Date().getFullYear();
            for (let year = currentYear; year >= 1950; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }
        }

        const locationSelect = document.getElementById('location');
        if (locationSelect && locationSelect.options.length <= 1) {
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
        }

        const brandSelect = document.getElementById('brand');
        if (brandSelect && brandSelect.options.length <= 1) {
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
        }
    }

    const addListingBtn = document.querySelector('[data-bs-target="#addListingModal"]');
    if (addListingBtn) {
        addListingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('addListingModal'));
            modal.show();
            initializeAddListingForm();
        });
    }

    const addListingModal = document.getElementById('addListingModal');
    if (addListingModal) {
        addListingModal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('addListingForm');
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                backdrop.remove();
            });

            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            if (form) {
                form.reset();
                if (dropzone) {
                    dropzone.removeAllFiles();
                }
                const submitButton = document.getElementById('submitButton');
                if (submitButton) {
                    submitButton.disabled = true;
                }
            }
        });
    }
}); 