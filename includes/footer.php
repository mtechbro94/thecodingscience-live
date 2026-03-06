</main>

<!-- Footer -->
<footer class="bg-dark text-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5>
                    <?php echo get_setting('site_name', SITE_NAME); ?>
                </h5>

                <p><strong>About Us:</strong> The Coding Science is a technology startup focused on online education,
                    trainings, and internships designed to bridge the gap between academia and industry.</p>
                <p class="small text-muted mb-0"><em>Making quality tech education accessible and inclusive for
                        all.</em></p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>What We Offer</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="fas fa-globe text-primary me-2"></i>
                        <strong>Online Learning:</strong> Study from anywhere at your own pace
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-language text-primary me-2"></i>
                        <strong>Regional Languages:</strong> Kashmiri, Hindi, Punjabi & more
                    </li>
                    <li>
                        <i class="fas fa-briefcase text-primary me-2"></i>
                        <strong>Industry Skills:</strong> Practical, real-world career preparation
                    </li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Quick Links & Contact</h5>
                <ul class="list-unstyled small mb-3 footer-links">
                    <li><a href="/" class="text-decoration-none"><strong>Home</strong></a></li>
                    <li><a href="/courses" class="text-decoration-none"><strong>Courses</strong></a></li>
                    <li><a href="/services" class="text-decoration-none"><strong>Live Trainings</strong></a></li>
                    <li><a href="/internships" class="text-decoration-none"><strong>Internships</strong></a></li>
                    <li><a href="/contact" class="text-decoration-none"><strong>Contact</strong></a></li>
                </ul>
                <p class="small mb-2">
                    <i class="fas fa-envelope me-2"></i> <strong>
                        <?php echo get_setting('contact_email', CONTACT_EMAIL); ?>
                    </strong><br>
                    <i class="fas fa-phone me-2"></i> <strong>
                        <?php echo get_setting('contact_phone', CONTACT_PHONE); ?>
                    </strong>
                </p>

                <div class="mt-2">
                    <?php if (get_setting('facebook_url')): ?>
                        <a href="<?php echo get_setting('facebook_url'); ?>" class="text-light me-3" target="_blank"><i
                                class="fab fa-facebook fa-lg"></i></a>
                    <?php endif; ?>
                    <?php if (get_setting('linkedin_url')): ?>
                        <a href="<?php echo get_setting('linkedin_url'); ?>" class="text-light me-3" target="_blank"><i
                                class="fab fa-linkedin fa-lg"></i></a>
                    <?php endif; ?>
                    <?php if (get_setting('instagram_url')): ?>
                        <a href="<?php echo get_setting('instagram_url'); ?>" class="text-light me-3" target="_blank"><i
                                class="fab fa-instagram fa-lg"></i></a>
                    <?php endif; ?>
                    <?php if (get_setting('youtube_url')): ?>
                        <a href="<?php echo get_setting('youtube_url'); ?>" class="text-light" target="_blank"><i
                                class="fab fa-youtube fa-lg"></i></a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <hr class="bg-light">
        <div class="text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?>
                <?php echo get_setting('site_name', SITE_NAME); ?>. All rights reserved.
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

<!-- Custom JS (with cache busting) -->
<script src="/assets/js/main.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/main.js'); ?>"></script>

<!-- Initialize AOS -->
<script>
    document.addEventListener('DOMContentLoaded', function  () {
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