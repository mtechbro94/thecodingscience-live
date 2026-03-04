<?php
// views/home.php

// Fetch Featured Courses from Database
$stmt = $pdo->query("SELECT * FROM courses WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 4");
$courses = $stmt->fetchAll();

// If no featured courses, fetch 4 latest courses
if (empty($courses)) {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 4");
    $courses = $stmt->fetchAll();
}

// Fetch Latest Blogs (Limit 3)
$stmt = $pdo->query("SELECT * FROM blogs WHERE is_published = 1 ORDER BY created_at DESC LIMIT 3");
$blogs = $stmt->fetchAll();

$page_title = "Home";
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<?php
$hero_bg = get_setting('hero_background', '');
$hero_style = '';
if (!empty($hero_bg)) {
    $hero_style = 'background-image: url(/assets/images/' . htmlspecialchars($hero_bg) . '?v=' . time() . ');';
}
?>
<section class="hero-section" style="<?php echo $hero_style; ?>">
    <div class="hero-overlay">
        <h1><?php echo get_setting('hero_title', 'School of Technology and AI Innovations'); ?></h1>
        <p class="hero-subtitle">
            <?php echo get_setting('hero_subtitle', 'Empowering the Youth of Jammu and Kashmir to lead the world in AI, Data Science and Emerging Technologies'); ?>
        </p>

        <a href="/courses" class="btn btn-primary btn-lg mt-4 shadow">Start Learning Now</a>
    </div>
</section>


<!-- About Section -->
<section class="py-5 bg-light about-section">
    <div class="container">
        <div class="row align-items-center gy-4">
            <div class="col-lg-6">
                <h2 class="about-heading mb-3">About
                    <?php echo SITE_NAME; ?>
                </h2>
                <p class="lead about-tagline">Bridge the gap between academic learning and industry requirements.</p>
                <p class="about-description">We are dedicated to providing world-class training in technology and
                    artificial intelligence. Our mission is to bridge the gap between academic learning and industry
                    requirements.</p>
                <ul class="about-features list-unstyled mt-4">
                    <li><i class="fas fa-check-circle text-primary"></i> <span>Expert instructors with 10+ years
                            experience</span></li>
                    <li><i class="fas fa-check-circle text-primary"></i> <span>Real-world projects and case
                            studies</span></li>
                    <li><i class="fas fa-check-circle text-primary"></i> <span>Job placement assistance</span></li>
                    <li><i class="fas fa-check-circle text-primary"></i> <span>Flexible learning schedules</span></li>
                    <li><i class="fas fa-check-circle text-primary"></i> <span>Affordable pricing and payment
                            plans</span></li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-lg about-card">
                    <div class="card-body p-4 p-lg-5">
                        <h4 class="card-title about-card-title mb-3"><i class="fas fa-eye text-primary me-2"></i>Our
                            Vision</h4>
                        <p class="about-card-text">To empower individuals with cutting-edge technology skills and
                            nurture the next generation of innovation leaders.</p>
                        <hr class="my-4">
                        <h4 class="card-title about-card-title mb-3"><i
                                class="fas fa-bullseye text-primary me-2"></i>Our Mission</h4>
                        <p class="about-card-text mb-0">Deliver comprehensive, industry-aligned training that transforms
                            careers and creates opportunities in the tech industry.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section -->
<section class="py-5 bg-light" id="blogs">
    <div class="container">
        <h2 class="text-center mb-5">Latest from Our Blog</h2>
        <div class="row justify-content-center">
            <?php foreach ($blogs as $index => $blog): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-lift">
                        <div class="overflow-hidden" style="height: 220px;">
                            <img src="<?php echo get_image_url($blog['image']); ?>" class="card-img-top"
                                alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                style="width: 100%; height: 100%; object-fit: cover;">
                        </div>

                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                    <?php echo ($index % 2 == 0) ? 'Technology' : 'Career'; ?>
                                </span>
                                <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i>
                                    <?php echo $blog['date']; ?>
                                </small>
                            </div>
                            <h5 class="card-title font-weight-bold mb-3">
                                <?php echo $blog['title']; ?>
                            </h5>
                            <p class="card-text text-muted mb-4">
                                <?php echo substr($blog['excerpt'], 0, 100) . '...'; ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 px-4 pb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="avatar px-2 py-1 bg-primary text-white rounded-circle me-2 font-weight-bold">
                                        <?php echo strtoupper(substr($blog['author'], 0, 1)); ?>
                                    </div>
                                    <small class="text-dark font-weight-bold">
                                        <?php echo htmlspecialchars($blog['author']); ?>
                                    </small>
                                </div>
                                <a href="/blog/<?php echo $blog['slug']; ?>"
                                    class="text-primary text-decoration-none font-weight-bold">Read <i
                                        class="fas fa-arrow-right ms-1"></i></a>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="/blogs" class="btn btn-outline-primary btn-lg px-5 rounded-pill shadow-sm">View All Articles</a>
        </div>
    </div>
</section>


<!-- Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose
            <?php echo SITE_NAME; ?>?
        </h2>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-user-tie fa-3x text-primary"></i>
                    </div>
                    <h4>Industry Experts</h4>
                    <p>Learn from experienced professionals with 10+ years in the tech industry.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-code fa-3x text-success"></i>
                    </div>
                    <h4>Hands-On Training</h4>
                    <p>Build real-world applications and portfolio-ready projects from day one.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-building fa-3x text-info"></i>
                    </div>
                    <h4>MNC Partnerships</h4>
                    <p>Tie-ups with leading companies for internships and placement assistance.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-stream fa-3x text-danger"></i>
                    </div>
                    <h4>Flexible Learning</h4>
                    <p>Live classes, recordings, and self-paced options to suit your schedule.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section class="py-5 section-muted" id="courses">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="mb-0">Featured Courses</h2>
            <a href="/courses" class="btn btn-outline-primary rounded-pill px-4">See All</a>
        </div>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm border-0 course-card hover-lift overflow-hidden">
                        <div class="position-relative">
                            <img src="<?php echo get_image_url($course['image']); ?>"
                                alt="<?php echo htmlspecialchars($course['name']); ?>" class="card-img-top"
                                style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span
                                    class="badge bg-primary rounded-pill shadow-sm px-3"><?php echo htmlspecialchars($course['level']); ?></span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-2">
                                <?php echo htmlspecialchars($course['name']); ?>
                            </h5>
                            <p class="card-text text-muted small mb-4">
                                <?php echo htmlspecialchars(substr($course['summary'], 0, 100)) . (strlen($course['summary']) > 100 ? '...' : ''); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="course-price">
                                    <span
                                        class="text-primary fw-bold h5 mb-0">₹<?php echo number_format($course['price']); ?></span>
                                </div>
                                <div class="course-duration small text-muted">
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo htmlspecialchars($course['duration'] ?: 'Self-paced'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 p-4 pt-0">
                            <a href="/courses" class="btn btn-primary w-100 rounded-pill">View
                                Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Student Success Stories -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Student Success Stories</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-light">
                    <div class="card-body p-4">
                        <div class="mb-3 text-warning">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="text-muted italic">“The Coding Science helped me switch from a non-tech background to
                            a
                            full-time web developer in just a few months.”</p>
                        <div class="d-flex align-items-center mt-4">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 40px; height: 40px;">AK</div>
                            <div>
                                <h6 class="mb-0">Ayesha Khan</h6>
                                <small class="text-muted">Software Developer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-light">
                    <div class="card-body p-4">
                        <div class="mb-3 text-warning">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="text-muted">“The projects and mentorship were exactly what I needed to crack my
                            first Data Science internship.”</p>
                        <div class="d-flex align-items-center mt-4">
                            <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 40px; height: 40px;">RS</div>
                            <div>
                                <h6 class="mb-0">Rohit Sharma</h6>
                                <small class="text-muted">Data Scientist</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-light">
                    <div class="card-body p-4">
                        <div class="mb-3 text-warning">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="text-muted">“Live classes, doubt support and career guidance made the learning
                            journey smooth and focused.”</p>
                        <div class="d-flex align-items-center mt-4">
                            <div class="avatar bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 40px; height: 40px;">SG</div>
                            <div>
                                <h6 class="mb-0">Simran Gupta</h6>
                                <small class="text-muted">AI Engineer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary position-relative overflow-hidden">
    <div class="container text-center py-4 position-relative z-index-1">
        <h2 class="mb-4 text-white fw-bold">Ready to Transform Your Career?</h2>
        <p class="lead mb-5 text-white-50">Join thousands of students who have successfully launched their tech careers
            with us.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="/courses" class="btn btn-light btn-lg rounded-pill px-5">Browse Courses</a>
            <a href="/contact" class="btn btn-outline-light btn-lg rounded-pill px-5">Contact Us</a>
        </div>
    </div>
</section>

<style>
    .course-card {
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }

    .hero-section {
        background-size: cover;
        background-position: center;
        height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        position: relative;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .feature-box {
        border-radius: 15px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        background: white;
        transition: all 0.3s ease;
    }

    .feature-box:hover {
        border-color: var(--bs-primary);
        transform: translateY(-5px);
    }
</style>

<?php require_once 'includes/footer.php'; ?>