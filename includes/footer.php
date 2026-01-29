    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5 mt-5">
        <div class="container-fluid px-4">
            <div class="row mb-4">
                <!-- About Section -->
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-shopping-bag me-2" style="color: var(--primary-color);"></i>
                        <?php echo getSetting('site_name', SITE_NAME); ?>
                    </h5>
                    <p class="text-muted small">
                        <?php echo htmlspecialchars(getSetting('about_us', 'Your trusted online shopping destination')); ?>
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-muted text-decoration-none small">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/products.php" class="text-muted text-decoration-none small">Products</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/about.php" class="text-muted text-decoration-none small">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/contact.php" class="text-muted text-decoration-none small">Contact Us</a></li>
                    </ul>
                </div>
                
                <!-- Policies -->
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Policies</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>/pages/privacy.php" class="text-muted text-decoration-none small">Privacy Policy</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/terms.php" class="text-muted text-decoration-none small">Terms & Conditions</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/refund.php" class="text-muted text-decoration-none small">Refund Policy</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/shipping.php" class="text-muted text-decoration-none small">Shipping Policy</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Contact Info</h5>
                    <p class="small text-muted mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        Email: <?php echo htmlspecialchars(getSetting('contact_email', ADMIN_EMAIL)); ?>
                    </p>
                    <p class="small text-muted mb-3">
                        <i class="fas fa-phone me-2"></i>
                        Phone: <?php echo htmlspecialchars(getSetting('contact_phone', '+91-0000000000')); ?>
                    </p>
                    
                    <!-- Social Links -->
                    <div class="social-links">
                        <a href="#" class="text-muted me-3" title="Facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-muted me-3" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-muted me-3" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-muted" title="LinkedIn">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="bg-secondary">
            
            <!-- Copyright -->
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small text-muted mb-0">
                        <?php echo htmlspecialchars(getSetting('footer_text', '© 2024 ' . SITE_NAME . '. All rights reserved.')); ?>
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="small text-muted mb-0">
                        Developed with <i class="fas fa-heart text-danger"></i> using PHP & MySQL
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($additionalScripts)): ?>
        <?php echo $additionalScripts; ?>
    <?php endif; ?>
</body>
</html>
