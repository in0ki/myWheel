document.addEventListener('DOMContentLoaded', function() {
    const swiper = new Swiper('.featured-cars-swiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    });

    fetch('api/featured_cars.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const swiperWrapper = document.querySelector('.featured-cars-swiper .swiper-wrapper');
                
                data.cars.forEach(car => {
                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide';
                    slide.innerHTML = `
                        <div class="card">
                            <img src="${car.image_url}" class="card-img-top" alt="${car.title}">
                            <div class="card-body">
                                <h5 class="card-title">${car.title}</h5>
                                <p class="card-text">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>${car.year}
                                    <br>
                                    <i class="fas fa-road text-primary me-2"></i>${car.mileage} км
                                    <br>
                                    <i class="fas fa-tag text-primary me-2"></i>${car.price} ${car.currency === 'USD' ? '$' : '₴'}
                                </p>
                                <a href="car.php?id=${car.id}" class="btn btn-primary w-100">
                                    Детальніше
                                </a>
                            </div>
                        </div>
                    `;
                    swiperWrapper.appendChild(slide);
                });

                swiper.update();
            }
        })
        .catch(error => console.error('Error loading featured cars:', error));
}); 