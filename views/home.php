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
$stmt = $pdo->query("
    SELECT b.*, u.name AS author_name, u.profile_image AS author_image 
    FROM blogs b 
    LEFT JOIN users u ON b.author_id = u.id 
    WHERE b.is_published = 1 
    ORDER BY b.created_at DESC 
    LIMIT 3
");
$blogs = $stmt->fetchAll();

// Fetch stats for animated counters
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn() ?: 0;

// Fetch Active Success Stories
try {
    $stmt = $pdo->query("SELECT * FROM success_stories WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");
    $dynamic_stories = $stmt->fetchAll();
} catch (PDOException $e) {
    $dynamic_stories = [];
}

$page_title = "Home";
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<?php
$hero_bg = get_setting('hero_background', '');
$hero_style = '';
if (!empty($hero_bg)) {
    $img_url = get_image_url($hero_bg) . '?v=' . time();
    $hero_style = 'background-image: url(\'' . $img_url . '\') !important; background-size: cover !important; background-position: center !important;';
}
?>
<section class="hero-section" style="<?php echo $hero_style; ?>">
    <div class="hero-overlay" data-aos="fade-up">
        <h1><?php echo get_setting('hero_title', 'School of Technology and AI Innovations'); ?></h1>
        <p class="hero-subtitle">
            <?php echo get_setting('hero_subtitle', 'Empowering the Youth of Jammu and Kashmir to lead the world in AI, Data Science and Emerging Technologies'); ?>
        </p>

        <a href="/courses" class="btn btn-primary btn-lg mt-4 shadow">Start Learning Now</a>
    </div>
</section>

<!-- Stats Counter Section -->
<section class="py-5 stats-section">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-counter-box">
                    <div class="stat-counter-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h2 class="stat-counter-number" data-target="5000">0</h2>
                    <span class="stat-counter-suffix">+</span>
                    <p class="stat-counter-label">Students Enrolled</p>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-counter-box">
                    <div class="stat-counter-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h2 class="stat-counter-number" data-target="<?php echo $total_courses; ?>">0</h2>
                    <span class="stat-counter-suffix">+</span>
                    <p class="stat-counter-label">Courses Available</p>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-counter-box">
                    <div class="stat-counter-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h2 class="stat-counter-number" data-target="500">0</h2>
                    <span class="stat-counter-suffix">+</span>
                    <p class="stat-counter-label">Certifications Issued</p>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-counter-box">
                    <div class="stat-counter-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h2 class="stat-counter-number" data-target="95">0</h2>
                    <span class="stat-counter-suffix">%</span>
                    <p class="stat-counter-label">Satisfaction Rate</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5 about-section">
    <div class="container">
        <div class="row align-items-center gy-4">
            <div class="col-lg-6" data-aos="fade-right">
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
                </ul>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
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

<!-- Featured Courses Section -->
<section class="py-5 section-muted" id="courses">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5" data-aos="fade-up">
            <h2 class="mb-0">Featured Courses</h2>
            <a href="/courses" class="btn btn-outline-primary rounded-pill px-4">See All</a>
        </div>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up">
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

<!-- Why Choose Us Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5" data-aos="fade-up">Why Choose
            <?php echo SITE_NAME; ?>?
        </h2>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-user-tie fa-3x text-primary"></i>
                    </div>
                    <h4>Industry Experts</h4>
                    <p>Learn from experienced professionals with 10+ years in the tech industry.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-code fa-3x text-success"></i>
                    </div>
                    <h4>Hands-On Training</h4>
                    <p>Build real-world applications and portfolio-ready projects from day one.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-building fa-3x text-info"></i>
                    </div>
                    <h4>MNC Partnerships</h4>
                    <p>Tie-ups with leading companies for internships and placement assistance.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
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

<!-- Blog Section -->
<?php if (!empty($blogs)): ?>
<section class="py-5 section-muted" id="blogs">
    <div class="container">
        <h2 class="text-center mb-5">Latest from Our Blog</h2>
        <div class="row justify-content-center">
            <?php foreach ($blogs as $index => $blog): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-lift" style="overflow: hidden; border-radius: 12px;">
                        <div style="height: 220px; overflow: hidden;">
                            <img src="<?php echo get_image_url($blog['image']); ?>"
                                alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        </div>

                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-primary border rounded-pill px-3 py-1">
                                    <?php echo htmlspecialchars($blog['category'] ?? 'Technology'); ?>
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
                                    <?php 
                                    $author_name = $blog['author_name'] ?? $blog['author'] ?? 'Admin';
                                    $author_data = [
                                        'profile_image' => $blog['author_image'],
                                        'name' => $author_name
                                    ];
                                    if (!empty($blog['author_image'])): ?>
                                        <img src="<?php echo get_avatar($author_data); ?>"
                                            alt="<?php echo htmlspecialchars($author_name); ?>"
                                            class="rounded-circle me-2 shadow-sm"
                                            style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="avatar px-2 py-1 bg-primary text-white rounded-circle me-2 font-weight-bold d-flex align-items-center justify-content-center"
                                            style="width:32px; height:32px; font-size: 0.8rem;">
                                            <?php echo strtoupper(substr($author_name, 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <small class="text-dark font-weight-bold">
                                        <?php echo htmlspecialchars($author_name); ?>
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
<?php endif; ?>

<!-- Student Success Stories (Carousel) -->
<?php if (!empty($dynamic_stories)): ?>
<section class="py-5 bg-light-subtle">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="fw-bold">Student Success Stories</h2>
            <p class="text-muted">Hear from our students who transformed their careers with us.</p>
        </div>
        
        <div id="successCarousel" class="carousel slide testimonial-carousel" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach (array_chunk($dynamic_stories, 3) as $index => $story_chunk): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="row g-4">
                            <?php foreach ($story_chunk as $story): ?>
                                <div class="col-md-4">
                                    <div class="card h-100 shadow-sm border-0 testimonial-card hover-lift">
                                        <div class="card-body p-4">
                                            <div class="mb-3 text-warning">
                                                <?php for ($i = 0; $i < ($story['rating'] ?? 5); $i++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="card-text mb-4 italic text-secondary">"<?php echo htmlspecialchars($story['content']); ?>"</p>
                                            <div class="d-flex align-items-center mt-auto">
                                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px; font-weight: bold;">
                                                    <?php echo strtoupper(substr($story['name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($story['name']); ?></h6>
                                                    <small class="text-primary"><?php echo htmlspecialchars($story['title']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Carousel Controls -->
            <div class="carousel-controls mt-5 d-flex justify-content-center gap-3">
                <button class="btn btn-outline-primary rounded-circle" type="button" data-bs-target="#successCarousel" data-bs-slide="prev" style="width: 45px; height: 45px;">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn btn-outline-primary rounded-circle" type="button" data-bs-target="#successCarousel" data-bs-slide="next" style="width: 45px; height: 45px;">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="fw-bold">Frequently Asked Questions</h2>
                    <p class="text-muted">Have questions? We're here to help you understand our programs better.</p>
                </div>
                
                <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden border" id="faqAccordion" data-aos="fade-up">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I enroll in a course?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                Enrolling is easy! Simply browse our courses, select the one you're interested in, and click the "Enroll Now" button. You'll be guided through the payment process via Razorpay.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Do I get a certificate after completion?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                Yes! Upon successful completion of all course modules and assignments, you will receive a verified certificate from The Coding Science that you can share on LinkedIn or with employers.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Are the courses live or recorded?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                Currently, all our flagship programs are conducted through interactive <strong>Live Sessions</strong> with industrial experts, allowing for real-time engagement and doubt clearing.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Is there placement assistance provided?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                Absolutely. We provide resume reviews, mock interviews, and refer our top-performing students to our network of partner companies for internship and job opportunities.
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

    /* Stats Counter Section */
    .stats-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, #312e81 100%);
        position: relative;
        overflow: hidden;
    }

    .stats-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }

    .stat-counter-box {
        padding: 2rem 1rem;
        text-align: center;
        position: relative;
    }

    .stat-counter-icon {
        font-size: 2.5rem;
        color: rgba(255,255,255,0.8);
        margin-bottom: 1rem;
    }

    .stat-counter-number {
        font-size: 3rem;
        font-weight: 800;
        color: #fff;
        display: inline;
        margin: 0;
        line-height: 1;
    }

    .stat-counter-suffix {
        font-size: 1.8rem;
        font-weight: 700;
        color: rgba(255,255,255,0.8);
    }

    .stat-counter-label {
        font-size: 0.95rem;
        color: rgba(255,255,255,0.7);
        margin-top: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
</style>

<script>
// Animated Counter
function animateCounters() {
    const counters = document.querySelectorAll('.stat-counter-number');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        
        const update = () => {
            current += step;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(update);
            } else {
                counter.textContent = target;
            }
        };
        
        update();
    });
}

// Trigger counters when section scrolls into view
const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    let counterStarted = false;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !counterStarted) {
                counterStarted = true;
                animateCounters();
            }
        });
    }, { threshold: 0.3 });
    observer.observe(statsSection);
}
</script>

<?php require_once 'includes/footer.php'; ?>