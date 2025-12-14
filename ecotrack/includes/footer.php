    <?php if (isset($_SESSION['points_earned']) && $_SESSION['points_earned'] > 0): ?>
    <div class="points-notification" id="pointsNotification">
        <span class="points-amount">+<?php echo $_SESSION['points_earned']; ?> Points!</span>
        <span class="points-message"><?php echo htmlspecialchars($_SESSION['points_message'] ?? 'Points earned!'); ?></span>
    </div>
    <?php 
    unset($_SESSION['points_earned']);
    unset($_SESSION['points_message']);
    endif; 
    ?>

    <footer class="site-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <a class="navbar-brand mb-3 d-block" href="index.php">
                        <i class="bi-globe-americas"></i>
                        <span>EcoTrack</span>
                    </a>
                    <p class="site-footer-link"><?php echo SITE_SLOGAN; ?></p>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="site-footer-title mb-3">Quick Links</h6>
                    <ul class="site-footer-links">
                        <li><a href="index.php" class="site-footer-link">Home</a></li>
                        <li><a href="about.php" class="site-footer-link">About Us</a></li>
                        <li><a href="contact.php" class="site-footer-link">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="site-footer-title mb-3">Modules</h6>
                    <ul class="site-footer-links">
                        <li><a href="modules/energy/index.php" class="site-footer-link">Energy Tracking</a></li>
                        <li><a href="modules/transport/index.php" class="site-footer-link">Transport</a></li>
                        <li><a href="modules/waste/index.php" class="site-footer-link">Waste Management</a></li>
                        <li><a href="modules/reports/index.php" class="site-footer-link">Report Trash</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="site-footer-title mb-3">Contact</h6>
                    <p class="site-footer-link mb-2">
                        <i class="bi-envelope me-2"></i>contact@ecotrack.com
                    </p>
                    <p class="site-footer-link">
                        <i class="bi-geo-alt me-2"></i>Tunis, Tunisia
                    </p>
                    <div class="mt-3">
                        <a href="#" class="site-footer-link me-3"><i class="bi-facebook"></i></a>
                        <a href="#" class="site-footer-link me-3"><i class="bi-twitter"></i></a>
                        <a href="#" class="site-footer-link me-3"><i class="bi-instagram"></i></a>
                        <a href="#" class="site-footer-link"><i class="bi-linkedin"></i></a>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p class="copyright-text">&copy; <?php echo date('Y'); ?> EcoTrack. All rights reserved. | Promoting sustainable living.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ROOT_PATH; ?>/js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var notification = document.getElementById('pointsNotification');
        if (notification) {
            setTimeout(function() {
                notification.remove();
            }, 3000);
        }
    });
    </script>
</body>
</html>
