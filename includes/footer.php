</main>

<!-- Modern Footer -->
<footer class="footer-modern py-5">
    <div class="container">
        <div class="row gy-4">
            <!-- Column 1: Brand & About -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="footer-title mb-4">
                    <?php echo get_setting('site_name', SITE_NAME); ?>
                </h5>
                <p class="footer-description mb-4">
                    The Coding Science is a technology startup focused on online education, trainings, and internships
                    designed to bridge the gap between academia and industry.
                </p>
                <p class="small text-muted mb-0"><em>Making quality tech education accessible and inclusive for
                        all.</em></p>
            </div>

            <!-- Column 2: What We Offer -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title mb-4">What We Offer</h5>
                <ul class="list-unstyled footer-offer-list">
                    <li class="mb-3">
                        <i class="fas fa-globe text-primary me-2"></i>
                        <span>Online Learning</span>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-language text-primary me-2"></i>
                        <span>Regional Languages</span>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-briefcase text-primary me-2"></i>
                        <span>Industry Skills</span>
                    </li>
                </ul>
            </div>

            <!-- Column 3: Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title mb-4">Quick Links</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="/" class="footer-link-item"><i class="fas fa-chevron-right me-2 small"></i>Home</a>
                    </li>
                    <li><a href="/courses" class="footer-link-item"><i
                                class="fas fa-chevron-right me-2 small"></i>Courses</a></li>
                    <li><a href="/services" class="footer-link-item"><i class="fas fa-chevron-right me-2 small"></i>Live
                            Trainings</a></li>
                    <li><a href="/internships" class="footer-link-item"><i
                                class="fas fa-chevron-right me-2 small"></i>Internships</a></li>
                    <li><a href="/contact" class="footer-link-item"><i
                                class="fas fa-chevron-right me-2 small"></i>Contact</a></li>
                </ul>
            </div>

            <!-- Column 4: Contact & Socials -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="footer-title mb-4">Connect With Us</h5>
                <div class="footer-contact-info mb-4">
                    <p class="mb-2">
                        <i class="fas fa-envelope me-2 text-primary"></i>
                        <strong><?php echo get_setting('contact_email', CONTACT_EMAIL); ?></strong>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <strong><?php echo get_setting('contact_phone', CONTACT_PHONE); ?></strong>
                    </p>
                </div>

                <div class="footer-social-wrapper">
                    <?php if (get_setting('facebook_url')): ?>
                        <a href="<?php echo get_setting('facebook_url'); ?>" class="footer-social-circle facebook"
                            target="_blank" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (get_setting('linkedin_url')): ?>
                        <a href="<?php echo get_setting('linkedin_url'); ?>" class="footer-social-circle linkedin"
                            target="_blank" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (get_setting('instagram_url')): ?>
                        <a href="<?php echo get_setting('instagram_url'); ?>" class="footer-social-circle instagram"
                            target="_blank" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (get_setting('youtube_url')): ?>
                        <a href="<?php echo get_setting('youtube_url'); ?>" class="footer-social-circle youtube"
                            target="_blank" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom mt-5 pt-4 border-top border-secondary text-center">
            <p class="mb-0 text-light small">
                &copy; <?php echo date('Y'); ?> <span class="text-white fw-bold"><?php echo get_setting('site_name', SITE_NAME); ?></span>. All rights reserved. | Developed by <span class="text-white fw-bold">The Coding Science Team</span>
            </p>
        </div>
    </div>
</footer>

<!-- WhatsApp Floating Widget -->
<?php
$whatsapp_number = get_setting('contact_phone', CONTACT_PHONE);
// Remove any non-numeric characters for the link
$whatsapp_number_clean = preg_replace('/[^0-9]/', '', $whatsapp_number);
?>
<a href="https://wa.me/<?php echo $whatsapp_number_clean; ?>" class="whatsapp-float" target="_blank"
    rel="noopener noreferrer" title="Chat with us on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom JS (with stronger cache busting) -->
<script src="/assets/js/main.js?v=<?php echo time(); ?>"></script>

<!-- Initialize AOS -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 50
        });
    });
</script>

<?php if (isset($extra_js))
    echo $extra_js; ?>
</body>

</html>