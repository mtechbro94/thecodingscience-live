<?php
// views/courses.php

$page_title = "Our Courses";
require_once 'includes/header.php';

// Individual Courses Data
$courses = [
    [
        'name' => 'Crash Course in Computer Science',
        'level' => 'Beginner',
        'price' => 2999,
        'description' => 'A foundational course designed for students starting their technology journey. It introduces how computers work, basic networking concepts, internet fundamentals, and problem-solving approaches used in computer science.',
        'icon' => 'fa-microchip',
        'color' => 'primary'
    ],
    [
        'name' => 'Programming with Python',
        'level' => 'Beginner to Intermediate',
        'price' => 3999,
        'description' => 'A practical programming course that teaches Python fundamentals, logic building, problem solving, and small real-world projects. This course serves as the foundation for advanced fields like data science, automation, and AI.',
        'icon' => 'fa-python',
        'color' => 'success'
    ],
    [
        'name' => 'Full Stack Web Development',
        'level' => 'Intermediate',
        'price' => 7999,
        'description' => 'A complete web development program covering frontend and backend technologies. Students learn HTML, CSS, JavaScript, backend logic, databases, and how to build and deploy real web applications.',
        'icon' => 'fa-globe',
        'color' => 'info'
    ],
    [
        'name' => 'Data Science from Scratch',
        'level' => 'Beginner to Intermediate',
        'price' => 6999,
        'description' => 'A hands-on introduction to data science covering data analysis, visualization, statistics basics, and working with real datasets using Python.',
        'icon' => 'fa-chart-bar',
        'color' => 'warning'
    ],
    [
        'name' => 'Machine Learning and AI Foundations',
        'level' => 'Intermediate to Advanced',
        'price' => 7999,
        'description' => 'A foundational machine learning course that introduces core algorithms, model training, evaluation techniques, and real-world AI applications.',
        'icon' => 'fa-brain',
        'color' => 'danger'
    ],
    [
        'name' => 'Ethical Hacking and Cybersecurity',
        'level' => 'Beginner to Intermediate',
        'price' => 6999,
        'description' => 'A cybersecurity course that teaches networking basics, common vulnerabilities, ethical hacking tools, and penetration testing concepts.',
        'icon' => 'fa-shield-alt',
        'color' => 'dark'
    ]
];

// Combo Programs Data
$combo_programs = [
    [
        'name' => 'Programming Starter Pack',
        'courses' => ['Crash Course in Computer Science', 'Programming with Python'],
        'original_price' => 6998,
        'price' => 4499,
        'description' => 'A perfect entry point for beginners to understand computer science fundamentals and learn their first programming language.',
        'badge' => 'Best for Beginners',
        'badge_color' => 'success'
    ],
    [
        'name' => 'Developer Career Pack',
        'courses' => ['Programming with Python', 'Full Stack Web Development'],
        'original_price' => 11998,
        'price' => 7999,
        'description' => 'Designed for students who want to become software developers and build real-world web applications.',
        'badge' => 'Most Popular',
        'badge_color' => 'primary'
    ],
    [
        'name' => 'AI and Data Science Career Track',
        'courses' => ['Programming with Python', 'Data Science from Scratch', 'Machine Learning and AI Foundations'],
        'original_price' => 18997,
        'price' => 11999,
        'description' => 'A complete learning pathway that prepares students for careers in artificial intelligence, machine learning, and data science.',
        'badge' => 'Best Value',
        'badge_color' => 'warning'
    ]
];

function getLevelBadgeClass($level) {
    if (strpos($level, 'Beginner') !== false && strpos($level, 'Intermediate') !== false) {
        return 'bg-success';
    } elseif (strpos($level, 'Beginner') !== false) {
        return 'bg-info';
    } elseif (strpos($level, 'Intermediate to Advanced') !== false) {
        return 'bg-warning text-dark';
    } elseif (strpos($level, 'Intermediate') !== false) {
        return 'bg-primary';
    }
    return 'bg-secondary';
}
?>

<style>
.bg-primary-subtle { background-color: #e0e7ff !important; }
.bg-success-subtle { background-color: #dcfce7 !important; }
.bg-warning-subtle { background-color: #fef3c7 !important; }
.bg-info-subtle { background-color: #cff4fc !important; }
.bg-dark-subtle { background-color: #e9ecef !important; }

.course-card {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    transition: all 0.3s ease;
    overflow: hidden;
}
.course-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    border-color: var(--primary-color);
}
.course-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.5rem;
}
.level-badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
}
.combo-card {
    border: 2px solid #e2e8f0;
    border-radius: 20px;
    transition: all 0.3s ease;
    background: linear-gradient(to bottom, #ffffff, #f8fafc);
}
.combo-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
}
.combo-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.5rem 1.5rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.85rem;
    white-space: nowrap;
}
.original-price {
    text-decoration: line-through;
    color: #94a3b8;
    font-size: 0.9rem;
}
.combo-price {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary-color);
}
.save-badge {
    background: #dcfce7;
    color: #166534;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.course-list-item {
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}
.section-header {
    position: relative;
    display: inline-block;
}
.section-header::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: var(--primary-color);
    border-radius: 2px;
}
</style>

<!-- Individual Courses Section -->
<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-3 fw-bold">Our Courses</h1>
            <p class="lead text-muted">Master the latest technologies with our industry-aligned curriculum designed for students from class 8 to university level.</p>
        </div>

        <!-- Level Legend -->
        <div class="d-flex justify-content-center gap-3 mb-5 flex-wrap">
            <span class="level-badge bg-info">Beginner</span>
            <span class="level-badge bg-success">Beginner to Intermediate</span>
            <span class="level-badge bg-primary">Intermediate</span>
            <span class="level-badge bg-warning text-dark">Intermediate to Advanced</span>
        </div>

        <div class="row g-4">
            <?php foreach ($courses as $index => $course): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 course-card shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="course-icon bg-<?php echo $course['color']; ?>-subtle text-<?php echo $course['color']; ?>">
                                    <i class="fas <?php echo $course['icon']; ?>"></i>
                                </div>
                                <span class="level-badge <?php echo getLevelBadgeClass($course['level']); ?>">
                                    <?php echo $course['level']; ?>
                                </span>
                            </div>
                            <h5 class="card-title fw-bold mb-3"><?php echo $course['name']; ?></h5>
                            <p class="card-text text-muted small mb-4"><?php echo $course['description']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="combo-price fs-4">₹<?php echo number_format($course['price']); ?></span>
                                </div>
                                <a href="/enroll?course=<?php echo urlencode($course['name']); ?>" class="btn btn-primary">Enroll Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Combo Programs Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">
                <span class="section-header">Career Track Programs</span>
            </h2>
            <p class="lead text-muted">Bundle courses together and save big! Our recommended learning pathways for your career success.</p>
            <span class="badge bg-warning text-dark fs-6 mt-2"><i class="fas fa-star me-1"></i> Best Value Programs</span>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($combo_programs as $index => $combo): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 combo-card shadow-sm position-relative">
                        <span class="combo-badge bg-<?php echo $combo['badge_color']; ?> text-white">
                            <i class="fas fa-star me-1"></i> <?php echo $combo['badge']; ?>
                        </span>
                        <div class="card-body p-4 pt-5">
                            <h5 class="card-title fw-bold text-center mb-3"><?php echo $combo['name']; ?></h5>
                            
                            <div class="mb-4">
                                <p class="text-muted small mb-2 fw-semibold">Includes:</p>
                                <?php foreach ($combo['courses'] as $course_name): ?>
                                    <div class="course-list-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <?php echo $course_name; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <p class="card-text text-muted small text-center mb-4"><?php echo $combo['description']; ?></p>
                            
                            <div class="text-center mb-4">
                                <span class="original-price">₹<?php echo number_format($combo['original_price']); ?></span>
                                <span class="save-badge ms-2">Save ₹<?php echo number_format($combo['original_price'] - $combo['price']); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="combo-price">₹<?php echo number_format($combo['price']); ?></span>
                                <a href="/enroll?combo=<?php echo urlencode($combo['name']); ?>" class="btn btn-primary btn-lg">
                                    Get This Bundle
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Why Choose Combos -->
        <div class="mt-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="bg-primary-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-percentage text-primary fs-4"></i>
                        </div>
                        <h6 class="fw-bold">Save Upto 40%</h6>
                        <p class="text-muted small mb-0">Get significant discounts when you bundle courses together</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="bg-success-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-certificate text-success fs-4"></i>
                        </div>
                        <h6 class="fw-bold">Complete Certification</h6>
                        <p class="text-muted small mb-0">Get certificates for each course upon completion</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-4">
                        <div class="bg-warning-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-infinity text-warning fs-4"></i>
                        </div>
                        <h6 class="fw-bold">Lifetime Access</h6>
                        <p class="text-muted small mb-0">Access course materials anytime, forever</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
