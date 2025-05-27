document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    let currentUser = null;

    // function updateAuthUI() {
    //     const authButtons = document.querySelector('.auth-buttons');
    //     const userMenu = document.querySelector('.user-menu');
        
    //     if (currentUser) {
    //         // User is logged in
    //         authButtons.style.display = 'none';
    //         userMenu.style.display = 'block';
    //         document.querySelector('.user-name').textContent = `${currentUser.firstName} ${currentUser.lastName}`;
    //     } else {
    //         // User is not logged in
    //         authButtons.style.display = 'block';
    //         userMenu.style.display = 'none';
    //     }
    // }
    function formatNumber(number) {
        return number ? number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") : "0";
      }

    function checkAuthState() {
        fetch('api/get_user.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentUser = data.user;
                    // updateAuthUI();
                } else {
                    currentUser = null;
                    // updateAuthUI();
                }
            })
            .catch(error => {
                console.error('Error checking auth state:', error);
                currentUser = null;
                // updateAuthUI();
            });
    }

    checkAuthState();

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            fetch('api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    checkAuthState();
                    const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                    loginModal.hide();
                    window.location.reload();
                    console.log('text')
                } else {
                    alert('Помилка входу: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Сталася помилка під час входу');
            });
        });
    }

    const logoutButton = document.getElementById('logoutButton');
    if (logoutButton) {
        logoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            fetch('api/logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentUser = null;
                        // updateAuthUI();
                    }
                })
                .catch(error => {
                    console.error('Error logging out:', error);
                });
        });
    }

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                alert('Паролі не збігаються');
                return;
            }

            fetch('api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    firstName: firstName,
                    lastName: lastName,
                    email: email,
                    password: password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Реєстрація успішна! Тепер ви можете увійти.');
                    document.querySelector('#authTabs a[href="#login"]').click();
                } else {
                    alert('Помилка реєстрації: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Сталася помилка під час реєстрації');
            });
        });
    }

    const chatModal = document.getElementById('chatModal');
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.getElementById('sendMessage');
    const chatMessages = document.getElementById('chatMessages');

    if (chatModal && messageInput && sendMessageBtn && chatMessages) {
        function loadMessages() {
            fetch('api/chat.php?action=get_messages')
                .then(response => response.json())
                .then(data => {
                    chatMessages.innerHTML = '';
                    data.messages.forEach(message => {
                        const messageElement = document.createElement('div');
                        messageElement.className = `message ${message.is_own ? 'own-message' : 'other-message'}`;
                        messageElement.innerHTML = `
                            <div class="message-content">
                                <div class="message-text">${message.text}</div>
                                <div class="message-time">${message.time}</div>
                            </div>
                        `;
                        chatMessages.appendChild(messageElement);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                })
                .catch(error => console.error('Error loading messages:', error));
        }

        function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            fetch('api/chat.php?action=send_message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    loadMessages();
                }
            })
            .catch(error => console.error('Error sending message:', error));
        }

        sendMessageBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        chatModal.addEventListener('show.bs.modal', loadMessages);
    }

    const carousel = document.getElementById('featuredCarsCarousel');
    if (carousel) {
        fetch('api/featured_cars.php')
            .then(response => response.json())
            .then(data => {
                const carouselInner = carousel.querySelector('.carousel-inner');
                data.cars.forEach((car, index) => {
                    const item = document.createElement('div');
                    item.className = `carousel-item ${index === 0 ? 'active' : ''}`;
                    item.innerHTML = `
                        <div class="card">
                            <img src="${car.image_url}" class="card-img-top" alt="${car.title}">
                            <div class="card-body">
                                <h5 class="card-title">${car.title}</h5>
                                <p class="card-text">${car.price} ₴</p>
                                <a href="car.php?id=${car.id}" class="btn btn-primary">Детальніше</a>
                            </div>
                        </div>
                    `;
                    carouselInner.appendChild(item);
                });
            })
            .catch(error => console.error('Error loading featured cars:', error));
    }

    function createListingCard(car) {
        return `
            <div class="col">
                <div class="card h-100">
                    ${car.main_photo 
                        ? `<img src="uploads/${car.main_photo}" 
                               class="card-img-top" 
                               alt="${car.brand} ${car.model}"
                               style="height: 200px; object-fit: cover;">`
                        : `<div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                               style="height: 200px;">
                             <img src="images/logo.jpg" alt="No photo" style="max-height: 80px; max-width: 80%; object-fit: contain;">
                           </div>`
                    }
                    <div class="card-body">
                        <h5 class="card-title">${car.brand} ${car.model}</h5>
                        <p class="card-text">
                            <strong>Рік:</strong> ${car.year}<br>
                            <strong>Пробіг:</strong> ${formatNumber(car.mileage)} км<br>
                            <strong>Ціна:</strong> ${formatNumber(car.price)} ${car.currency}
                        </p>
                        <a href="car.php?id=${car.id}" class="btn btn-outline-primary">Детальніше</a>
                    </div>
                </div>
            </div>
        `;
    }
}); 