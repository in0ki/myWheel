    <!-- </main> -->
    <?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

    <button class="chat-button" data-bs-toggle="modal" data-bs-target="#chatModal">
        <i class="fas fa-comments"></i>
    </button>

    <div class="modal fade" id="chatModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Чат підтримки</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="chat-messages" id="chatMessages">
                    </div>
                    <div class="chat-input mt-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="messageInput" placeholder="Введіть повідомлення...">
                            <button class="btn btn-primary" id="sendMessage">Відправити</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer bg-light py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Про нас</h5>
                    <p>АвтоПродажа - це зручна платформа для купівлі та продажу автомобілів в Україні.</p>
                </div>
                <div class="col-md-4">
                    <h5>Контакти</h5>
                    <p>
                        <i class="fas fa-envelope me-2"></i>info@mywheels.com<br>
                        <i class="fas fa-phone me-2"></i>+380 44 123 4567
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Ми в соцмережах</h5>
                    <div class="social-links">
                        <a href="#" class="text-dark me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-dark me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-dark me-3"><i class="fab fa-telegram"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> MyWheels. Всі права захищені.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <script src="js/main.js"></script>
    <script src="js/modal.js"></script>
    <script src="js/add-listing.js"></script>

    <?php if ($current_page === 'index.php'): ?>
    <script src="js/featured-cars.js"></script>
    
        
    <?php elseif ($current_page === 'car.php'): ?>
        <script src="js/car-details.js"></script>
    <?php endif; ?>

    <script src="js/fake-chat.js"></script>
</body>
</html> 