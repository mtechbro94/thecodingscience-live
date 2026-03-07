<?php
// views/internships.php - Student Internships Only

require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Student Internships";

// Fetch only industrial internships for students
$internships = get_internships_by_category('industrial');

require_once 'includes/header.php';
?>

<style>
    /* Main Section Styling */
    .internship-section {
        padding: 80px 0;
    }
    .section-header {
        text-align: center;
        margin-bottom: 60px;
    }
    .section-header h2 {
        font-size: 2.8rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 15px;
    }
    .section-header i {
        margin-right: 15px;
        color: #2AF598;
    }
    
    /* Info Card Styling */
    .internship-info-card {
        background: white;
        border-radius: 20px;
        padding: 50px;
        margin-bottom: 50px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        border-left: 6px solid #2AF598;
        transition: all 0.3s ease;
        position: relative;
        z-index: 10;
        background: linear-gradient(135deg, rgba(42, 245, 152, 0.08) 0%, rgba(0, 146, 69, 0.08) 100%);
    }
    .internship-info-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    .info-card-inner {
        display: flex;
        gap: 35px;
        align-items: flex-start;
    }
    .info-card-icon {
        font-size: 4.5rem;
        flex-shrink: 0;
        line-height: 1;
        color: #2AF598;
    }
    .info-card-content h5 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 15px;
    }
    .info-card-content p {
        color: #555;
        line-height: 1.8;
        margin-bottom: 20px;
        font-size: 1.05rem;
    }
    .info-card-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .info-card-list li {
        padding: 12px 0 12px 32px;
        position: relative;
        color: #666;
        line-height: 1.8;
        font-size: 0.98rem;
        font-weight: 500;
    }
    .info-card-list li::before {
        content: '✓';
        position: absolute;
        left: 0;
        font-weight: bold;
        font-size: 1.5rem;
        color: #2AF598;
    }
    
    /* Internship Card Styling */
    .internship-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        overflow: hidden;
        background-color: #fff;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .internship-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.18) !important;
    }
    .internship-header {
        padding: 40px 25px;
        background: linear-gradient(135deg, #2AF598 0%, #009245 100%);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        position: relative;
        overflow: hidden;
    }
    .internship-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        transition: all 0.3s ease;
    }
    .internship-card:hover .internship-header::before {
        top: -30%;
        right: -30%;
    }
    .internship-header-content {
        position: relative;
        z-index: 1;
        text-align: center;
    }
    .internship-icon {
        width: 85px;
        height: 85px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 45px;
        background: rgba(255,255,255,0.25);
        margin: 0 auto 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }
    .internship-card:hover .internship-icon {
        transform: scale(1.15) rotate(8deg);
        background: rgba(255,255,255,0.35);
    }
    .internship-header h3 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .internship-meta {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: rgba(255,255,255,0.95);
        font-size: 0.97rem;
        margin-top: 14px;
        font-weight: 500;
    }
    .internship-card .card-body {
        padding: 35px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .internship-card .card-body p {
        line-height: 1.7;
        color: #666;
        margin-bottom: 20px;
    }
    .internship-card .card-body h6 {
        color: #333;
        margin-bottom: 15px;
        font-size: 0.98rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .skills-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 25px;
    }
    .skills-list .badge {
        padding: 8px 16px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.8rem;
        background: linear-gradient(135deg, #2AF598 0%, #009245 100%);
        color: white;
        transition: all 0.2s ease;
    }
    .skills-list .badge:hover {
        transform: scale(1.05);
    }
    .apply-btn {
        padding: 14px 32px;
        font-weight: 700;
        border-radius: 50px;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #2AF598 0%, #009245 100%);
        border: none;
        color: white;
        margin-top: auto;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.8px;
    }
    .apply-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(42, 245, 152, 0.4);
        color: white;
    }
    
    /* Why Choose Section */
    .why-choose-section {
        background: linear-gradient(135deg, #2AF598 0%, #009245 100%);
        border-radius: 20px;
        padding: 80px 50px;
        color: white;
        margin-top: 80px;
    }
    .why-choose-section h3 {
        font-size: 2.4rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-align: center;
    }
    .why-choose-section .subtitle {
        font-size: 1.15rem;
        opacity: 0.95;
        margin-bottom: 60px;
        text-align: center;
    }
    .why-choose-item {
        text-align: center;
        padding: 25px;
        transition: all 0.3s ease;
        border-radius: 15px;
        background: rgba(255,255,255,0.1);
        padding: 35px;
    }
    .why-choose-item:hover {
        transform: translateY(-12px);
        background: rgba(255,255,255,0.15);
    }
    .why-choose-item i {
        font-size: 3.5rem;
        margin-bottom: 20px;
        opacity: 0.95;
    }
    .why-choose-item h5 {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .why-choose-item p {
        font-size: 0.98rem;
        opacity: 0.9;
    }
    
    /* Hero Section */
    .internship-hero {
        background: linear-gradient(135deg, rgba(42, 245, 152, 0.15) 0%, rgba(0, 146, 69, 0.1) 100%);
        padding: 80px 0;
        text-align: center;
        margin-top: 80px;
        margin-bottom: 40px;
        border-radius: 15px;
    }
    .internship-hero h1 {
        font-size: 3.2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 15px;
    }
    .internship-hero p {
        font-size: 1.25rem;
        color: #666;
        margin-bottom: 0;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-state i {
        font-size: 4rem;
        color: #2AF598;
        margin-bottom: 20px;
    }
    .empty-state h3 {
        color: #333;
        margin-bottom: 10px;
    }
    .empty-state p {
        color: #999;
        font-size: 1.05rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .section-header h2 {
            font-size: 2.2rem;
        }
        .internship-info-card {
            padding: 35px 25px;
        }
        .info-card-inner {
            flex-direction: column;
            gap: 25px;
            align-items: center;
            text-align: center;
        }
        .info-card-icon {
            font-size: 3.5rem;
        }
        .info-card-list li {
            padding-left: 28px;
        }
        .why-choose-section {
            padding: 50px 30px;
            margin-top: 60px;
        }
        .why-choose-section h3 {
            font-size: 1.8rem;
        }
        .internship-hero {
            padding: 50px 20px;
            margin-top: 60px;
        }
        .internship-hero h1 {
            font-size: 2rem;
        }
        .internship-hero p {
            font-size: 1rem;
        }
    }
</style>

<!-- Hero Section -->
<div class="container internship-hero">
    <h1 class="mb-3 fw-bold">Student Internship Opportunities</h1>
    <p>Launch your career with hands-on experience. Learn real-world technologies and build your professional portfolio with industry mentors.</p>
</div>

<section class="internship-section">
    <div class="container">
        <!-- Info Card -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-11">
                <div class="internship-info-card">
                    <div class="info-card-inner">
                        <div class="info-card-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div class="info-card-content">
                            <h5>Start Your Journey with Real Projects</h5>
                            <p>Our internship program is designed to accelerate your professional development through practical, hands-on experience in real-world projects.</p>
                            <ul class="info-card-list">
                                <li><strong>Project-Based Learning:</strong> Work on live projects with real clients and real impact on your resume</li>
                                <li><strong>Hands-On Experience:</strong> Master the latest technologies and industry best practices from day one</li>
                                <li><strong>Expert Mentorship:</strong> Get guidance from experienced professionals who care about your growth</li>
                                <li><strong>Career Acceleration:</strong> Build a portfolio that stands out to employers and opens doors</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Internships Cards -->
        <?php if (!empty($internships)): ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($internships as $internship): ?>
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="card h-100 internship-card">
                            <div class="internship-header">
                                <div class="internship-header-content">
                                    <div class="internship-icon mx-auto">
                                        <i class="fas fa-laptop-code"></i>
                                    </div>
                                    <h3><?php echo htmlspecialchars($internship['title']); ?></h3>
                                    <div class="internship-meta">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span><?php echo htmlspecialchars($internship['duration']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><?php echo htmlspecialchars($internship['description']); ?></p>
                                <h6>Skills You'll Learn:</h6>
                                <div class="skills-list mb-4">
                                    <?php
                                    $skills = array_filter(array_map('trim', explode(',', $internship['skills_covered'])));
                                    foreach (array_slice($skills, 0, 4) as $skill) {
                                        echo '<span class="badge">' . htmlspecialchars($skill) . '</span>';
                                    }
                                    if (count($skills) > 4) {
                                        echo '<span class="badge">+' . (count($skills) - 4) . ' more</span>';
                                    }
                                    ?>
                                </div>
                                <div class="d-grid mt-auto">
                                    <a href="<?php echo htmlspecialchars($internship['google_form_link']); ?>" target="_blank" class="btn apply-btn">
                                        <i class="fas fa-arrow-right me-2"></i>Apply Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Internships Available Now</h3>
                <p>Check back soon! We're always adding new opportunities for talented students.</p>
            </div>
        <?php endif; ?>
        
        <!-- Why Choose Section -->
        <div class="why-choose-section">
            <h3>Why Choose The Coding Science Internships?</h3>
            <p class="subtitle">We're committed to your professional growth and success</p>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="why-choose-item">
                        <i class="fas fa-star"></i>
                        <h5>Quality Projects</h5>
                        <p>Work on real projects with actual business impact</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="why-choose-item">
                        <i class="fas fa-users"></i>
                        <h5>Expert Mentors</h5>
                        <p>Learn from senior professionals in the industry</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="why-choose-item">
                        <i class="fas fa-certificate"></i>
                        <h5>Certification</h5>
                        <p>Earn recognized credentials to boost your resume</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="why-choose-item">
                        <i class="fas fa-handshake"></i>
                        <h5>Job Placement</h5>
                        <p>Strong performers may get job offers post-internship</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
