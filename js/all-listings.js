document.addEventListener('DOMContentLoaded', function() {
    const listingsContainer = document.getElementById('listingsContainer');
    const paginationContainer = document.getElementById('listingsPagination');
    let currentPage = 1;

    function loadListings(page) {
        fetch(`api/get_listings.php?page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    listingsContainer.innerHTML = '';

                    data.listings.forEach(listing => {
                        const col = document.createElement('div');
                        col.className = 'col';
                        col.innerHTML = `
                            <div class="card h-100">
                                <img src="${listing.image_url}" class="card-img-top" alt="${listing.title}">
                                <div class="card-body">
                                    <h5 class="card-title">${listing.title}</h5>
                                    <p class="card-text">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>${listing.year}
                                        <br>
                                        <i class="fas fa-road text-primary me-2"></i>${listing.mileage} км
                                        <br>
                                        <i class="fas fa-tag text-primary me-2"></i>${listing.price} ${listing.currency === 'USD' ? '$' : '₴'}
                                    </p>
                                    <a href="car.php?id=${listing.id}" class="btn btn-primary w-100">
                                        Детальніше
                                    </a>
                                </div>
                            </div>
                        `;
                        listingsContainer.appendChild(col);
                    });

                    updatePagination(data.pagination);
                }
            })
            .catch(error => console.error('Error loading listings:', error));
    }

    function updatePagination(pagination) {
        paginationContainer.innerHTML = '';

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${pagination.current_page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `
            <a class="page-link" href="#" aria-label="Previous" data-page="${pagination.current_page - 1}">
                <span aria-hidden="true">&laquo;</span>
            </a>
        `;
        paginationContainer.appendChild(prevLi);

        for (let i = 1; i <= pagination.total_pages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === pagination.current_page ? 'active' : ''}`;
            li.innerHTML = `
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            `;
            paginationContainer.appendChild(li);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}`;
        nextLi.innerHTML = `
            <a class="page-link" href="#" aria-label="Next" data-page="${pagination.current_page + 1}">
                <span aria-hidden="true">&raquo;</span>
            </a>
        `;
        paginationContainer.appendChild(nextLi);

        paginationContainer.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page && page !== pagination.current_page) {
                    currentPage = page;
                    loadListings(page);
                    document.querySelector('.all-listings').scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    loadListings(currentPage);
}); 