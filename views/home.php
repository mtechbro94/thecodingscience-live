<?php
// views/home.php

// Fetch Featured Courses (Limit 4)
$stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 4");
$courses = $stmt->fetchAll();

// Fetch Latest Blogs (Limit 3)
$stmt = $pdo->query("SELECT * FROM blogs WHERE is_published = 1 ORDER BY created_at DESC LIMIT 3");
$blogs = $stmt->fetchAll();

$page_title = "Home";
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
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
                        <div class="overflow-hidden">
                            <img src="<?php echo get_image_url($blog['image']); ?>" class="card-img-top zoom-effect"
                                alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                style="height: 220px; object-fit: cover;" loading="lazy" decoding="async">
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
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-user-tie fa-2x text-primary"></i>
                    </div>
                    <h4>Industry Expert Trainers</h4>
                    <p>Learn from experienced professionals with 10+ years in the tech industry. Our trainers work on
                        real-world projects.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-code fa-2x text-success"></i>
                    </div>
                    <h4>Hands-On Projects</h4>
                    <p>Build real-world applications from day one. Portfolio-ready projects that showcase your skills to
                        employers.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-building fa-2x text-info"></i>
                    </div>
                    <h4>Tie ups with MNCs</h4>
                    <p>We have partnerships with leading multinational companies for internships, placements, and
                        industry exposure.
                    </p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-stream fa-2x text-danger"></i>
                    </div>
                    <h4>Flexible Learning</h4>
                    <p>Live classes, recorded sessions, and self-paced learning options. Learn at your own pace and
                        schedule.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section class="py-5 section-muted" id="courses">
    <div class="container">
        <h2 class="text-center mb-5">Featured Courses</h2>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm course-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php echo $course['name']; ?>
                            </h5>
                            <p class="card-text text-muted small">
                                <?php
                                $desc = $course['summary'] ?: $course['description'];
                                echo substr($desc, 0, 100) . '...';
                                ?>
                            </p>
                            <div class="course-meta">
                                <small><i class="fas fa-clock"></i>
                                    <?php echo $course['duration']; ?>
                                </small><br>
                                <small><i class="fas fa-rupee-sign"></i> ₹
                                    <?php echo $course['price']; ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="/course/<?php echo $course['id']; ?>" class="btn btn-primary btn-sm w-100">View
                                Course</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="/courses" class="btn btn-primary btn-lg">Explore All Courses</a>
        </div>
    </div>
</section>

<!-- Student Success Stories -->
<section class="py-5 section-highlight">
    <div class="container">
        <h2 class="text-center mb-5">Student Success Stories</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="small text-muted">“The Coding Science helped me switch from a non-tech background to a
                            full-time web developer in just a few months.”</p>
                        <h6 class="mt-3 mb-0">Ayesha Khan</h6>
                        <small class="text-muted">Front-end Developer, Bangalore</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="small text-muted">“The projects and mentorship were exactly what I needed to crack my
                            first Data Science internship.”</p>
                        <h6 class="mt-3 mb-0">Rohit Sharma</h6>
                        <small class="text-muted">Data Science Intern, Pune</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <p class="small text-muted">“Live classes, doubt support and career guidance made the learning
                            journey smooth and focused.”</p>
                        <h6 class="mt-3 mb-0">Simran Gupta</h6>
                        <small class="text-muted">AI & ML Enthusiast, Jammu & Kashmir</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary">
    <div class="container text-center">
        <h2 class="mb-4 text-light">Ready to Transform Your Career?</h2>
        <p class="lead mb-4 text-light">Join thousands of students who have successfully launched their tech careers.
        </p>
        <a href="/contact" class="btn btn-light btn-lg">Get in Touch Today</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>