
<!-- Footer -->
<footer class="site-footer mt-5">
    <div class="footer-top">
        <div class="container">
            <div class="row gy-4">
                <!-- Brand -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="/assets/images/logo.png" alt="<?= SITE_NAME ?>" height="60">
                        <div>
                            <div class="footer-brand-name"><?= SITE_NAME ?></div>
                            <div class="footer-brand-tag"><?= SITE_TAGLINE ?></div>
                        </div>
                    </div>
                    <p class="footer-desc">
                        Brainstorm School is committed to nurturing brilliant minds, building character, and preparing students for a future of excellence. We combine academic rigour with real-world skills.
                    </p>
                    <div class="social-links mt-3">
                        <a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank" class="social-link whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="social-link facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link twitter"><i class="fab fa-x-twitter"></i></a>
                        <a href="#" class="social-link youtube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="/">Home</a></li>
                        <li><a href="/about.php">About Us</a></li>
                        <li><a href="/admissions.php">Admissions</a></li>
                        <li><a href="/results.php">Check Results</a></li>
                        <li><a href="/gallery.php">Gallery</a></li>
                        <li><a href="/contact.php">Contact</a></li>
                    </ul>
                </div>

                <!-- Academics -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Academics</h6>
                    <ul class="footer-links">
                        <li><a href="/about.php#curriculum">Curriculum</a></li>
                        <li><a href="/about.php#nursery">Nursery & Primary</a></li>
                        <li><a href="/about.php#junior">Junior Secondary</a></li>

                        <li><a href="/news.php">News & Updates</a></li>
                        <li><a href="/events.php">Events Calendar</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-heading">Contact Information</h6>
                    <ul class="footer-contact-list">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= SITE_ADDRESS ?></span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <a href="tel:<?= SITE_PHONE ?>"><?= SITE_PHONE ?></a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>
                        </li>
                        <li>
                            <i class="fab fa-whatsapp"></i>
                            <a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank">Chat us on WhatsApp</a>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <p class="text-muted small mb-1"><i class="fas fa-clock me-2"></i><strong>School Hours:</strong></p>
                        <p class="text-muted small mb-0">Mon – Fri: 7:30am – 3:00pm</p>
                        <p class="text-muted small">Sat (Extra Classes): 8:00am – 12:00pm</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 small">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 small">Designed with care &mdash; <?= SITE_TAGLINE ?></p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank" class="whatsapp-float" title="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="/assets/js/main.js"></script>
<?= isset($extraScripts) ? $extraScripts : '' ?>
</body>
</html>
