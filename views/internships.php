<?php
// views/internships.php

$page_title = "Internships";
require_once 'includes/header.php';

// Internships List
$internships = [
    [
        'id' => 1,
        'role' => 'Web Development Trainer',
        'company' => 'The Coding Science',
        'duration' => '1 Month Internship',
        'location' => 'Remote',
        'price' => 2999,
        'description' => 'Become a certified Web Development Trainer! Learn to teach HTML, CSS, JavaScript, React, Node.js & MongoDB. After completing the internship, you\'ll have the opportunity to work full-time with us as a professional trainer.',
        'icon' => 'fa-globe',
        'color' => 'primary',
        'features' => [
            'Teach modern web development',
            'Live project experience',
            'Mentorship from industry experts',
            'Certificate upon completion',
            'Chance to work full-time after 1 month'
        ]
    ],
    [
        'id' => 2,
        'role' => 'Python Trainer',
        'company' => 'The Coding Science',
        'duration' => '1 Month Internship',
        'location' => 'Remote',
        'price' => 2999,
        'description' => 'Master Python programming and become a certified Python Trainer! Learn Python fundamentals, Django, Flask, data science basics & AI concepts. After completing the internship, you\'ll have the opportunity to work full-time with us as a professional trainer.',
        'icon' => 'fa-python',
        'color' => 'success',
        'features' => [
            'Teach Python programming',
            'Backend development with Django/Flask',
            'Data Science & AI fundamentals',
            'Certificate upon completion',
            'Chance to work full-time after 1 month'
        ]
    ],
    [
        'id' => 3,
        'role' => 'Data Science & AI Trainer',
        'company' => 'The Coding Science',
        'duration' => '1 Month Internship',
        'location' => 'Remote',
        'price' => 2999,
        'description' => 'Become a certified Data Science & AI Trainer! Learn machine learning, deep learning, data visualization, and AI technologies. After completing the internship, you\'ll have the opportunity to work full-time with us as a professional trainer.',
        'icon' => 'fa-brain',
        'color' => 'warning',
        'features' => [
            'Teach Data Science & ML',
            'Hands-on AI projects',
            'Real-world dataset analysis',
            'Certificate upon completion',
            'Chance to work full-time after 1 month'
        ]
    ]
];
?>

<style>
    .internship-card {
        border: none;
        border-radius: 20px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .internship-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
    }
    .internship-header {
        padding: 25px;
        color: white;
    }
    .internship-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        background: rgba(255,255,255,0.2);
    }
    .feature-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .feature-list li {
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .feature-list li:last-child {
        border-bottom: none;
    }
    .feature-list li i {
        color: #28a745;
        margin-right: 10px;
    }
    .price-tag {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary-color);
    }
    .fulltime-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 600;
        display: inline-block;
    }
    .apply-btn {
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
    }
    .apply-btn:hover {
        transform: scale(1.05);
    }
    .hero-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .tech-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f8f9fa;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-3 fw-bold">Train with Us, Work with Us</h1>
            <p class="lead text-muted">Become a certified trainer in Web Development, Python, or Data Science & AI. Get paid while you learn, and earn a full-time opportunity after completing your internship!</p>
            <div class="mt-4">
                <span class="fulltime-badge">
                    <i class="fas fa-star me-2"></i>Get Full-Time Opportunity After 1 Month!
                </span>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($internships as $internship): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 internship-card shadow-lg">
                        <div class="internship-header bg-<?php echo $internship['color']; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="internship-icon">
                                    <i class="fas <?php echo $internship['icon']; ?>"></i>
                                </div>
                                <span class="badge bg-white text-<?php echo $internship['color']; ?> fw-bold">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo $internship['duration']; ?>
                                </span>
                            </div>
                            <h3 class="mt-3 fw-bold"><?php echo $internship['role']; ?></h3>
                            <p class="mb-0 opacity-75"><i class="fas fa-building me-2"></i><?php echo $internship['company']; ?></p>
                        </div>
                        
                        <div class="card-body p-4">
                            <p class="text-muted mb-4"><?php echo $internship['description']; ?></p>
                            
                            <h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-success me-2"></i>What You'll Learn:</h6>
                            <ul class="feature-list mb-4">
                                <?php foreach ($internship['features'] as $feature): ?>
                                    <li><i class="fas fa-check"></i><?php echo $feature; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <div class="text-center mb-4">
                                <span class="price-tag">₹<?php echo number_format($internship['price']); ?></span>
                                <span class="text-muted">/month</span>
                            </div>
                            
                            <div class="d-grid">
                                <a href="/apply-internship/<?php echo $internship['id']; ?>" 
                                   class="btn btn-<?php echo $internship['color']; ?> apply-btn">
                                    <i class="fas fa-paper-plane me-2"></i>Apply Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-5 text-center bg-light p-5 rounded">
            <h3>Why Join The Coding Science?</h3>
            <p class="mb-4">We provide the best training with real-world projects and expert mentors.</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <i class="fas fa-certificate fa-3x text-primary mb-3"></i>
                    <h5>Get Certified</h5>
                    <p class="text-muted mb-0">Earn industry-recognized certificates</p>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                    <h5>Full-Time Opportunity</h5>
                    <p class="text-muted mb-0">Work with us after successful internship</p>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-laptop fa-3x text-warning mb-3"></i>
                    <h5>Remote Work</h5>
                    <p class="text-muted mb-0">Work from anywhere in the world</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
