<?php
// views/internships.php

require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Internships";

// Fetch internships from the database
$teaching_internships = get_internships_by_category('teaching');
$industrial_internships = get_internships_by_category('industrial');

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
    }
    
    /* Info Card Styling */
    .internship-info-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        margin-bottom: 50px;
        box-shadow: 0 8px 35px rgba(0,0,0,0.1);
        border-left: 6px solid;
        transition: all 0.3s ease;
        position: relative;
        z-index: 10;
    }
    .internship-info-card.teaching {
        border-left-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
    }
    .internship-info-card.industrial {
        border-left-color: #2AF598;
        background: linear-gradient(135deg, rgba(42, 245, 152, 0.08) 0%, rgba(0, 146, 69, 0.08) 100%);
    }
    .internship-info-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    .info-card-inner {
        display: flex;
        gap: 30px;
        align-items: flex-start;
    }
    .info-card-icon {
        font-size: 4rem;
        flex-shrink: 0;
        line-height: 1;
    }
    .info-card-content h5 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 12px;
    }
    .info-card-content p {
        color: #555;
        line-height: 1.8;
        margin-bottom: 15px;
        font-size: 1rem;
    }
    .info-card-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .info-card-list li {
        padding: 10px 0 10px 30px;
        position: relative;
        color: #666;
        line-height: 1.7;
        font-size: 0.95rem;
    }
    .info-card-list li::before {
        content: '✓';
        position: absolute;
        left: 0;
        font-weight: bold;
        font-size: 1.3rem;
    }
    .teaching .info-card-list li::before {
        color: #667eea;
    }
    .industrial .info-card-list li::before {
        color: #2AF598;
    }
    
    /* Internship Card Styling */
    .internship-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        overflow: hidden;
        background-color: #fff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .internship-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15) !important;
    }
    .internship-header {
        padding: 40px 25px;
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
    .internship-header.bg-primary { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
    }
    .internship-header.bg-success { 
        background: linear-gradient(135deg, #2AF598 0%, #009245 100%); 
    }
    .internship-header-content {
        position: relative;
        z-index: 1;
        text-align: center;
    }
    .internship-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        background: rgba(255,255,255,0.25);
        margin: 0 auto 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }
    .internship-card:hover .internship-icon {
        transform: scale(1.1) rotate(5deg);
        background: rgba(255,255,255,0.35);
    }
    .internship-header h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }
    .internship-meta {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: rgba(255,255,255,0.9);
        font-size: 0.95rem;
        margin-top: 12px;
        font-weight: 500;
    }
    .internship-card .card-body {
        padding: 30px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .internship-card .card-body p {
        line-height: 1.6;
        color: #555;
    }
    .internship-card .card-body h6 {
        color: #333;
        margin-bottom: 12px;
        font-size: 0.95rem;
        font-weight: 600;
    }
    .skills-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    .skills-list .badge {
        padding: 8px 14px;
        border-radius: 25px;
        font-weight: 500;
        font-size: 0.85rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .apply-btn {
        padding: 14px 32px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        margin-top: auto;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }
    .apply-btn:hover {
        opacity: 0.9;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }
    
    /* Why Choose Section */
    .why-choose-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 70px 50px;
        color: white;
        margin-top: 70px;
    }
    .why-choose-section h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-align: center;
    }
    .why-choose-section .subtitle {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 50px;
        text-align: center;
    }
    .why-choose-item {
        text-align: center;
        padding: 20px;
        transition: all 0.3s ease;
    }
    .why-choose-item:hover {
        transform: translateY(-10px);
    }
    .why-choose-item i {
        font-size: 3rem;
        margin-bottom: 20px;
        opacity: 0.95;
    }
    .why-choose-item h5 {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    .why-choose-item p {
        font-size: 0.95rem;
        opacity: 0.9;
    }
    
    /* Hero Section */
    .internship-hero {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(42, 245, 152, 0.1) 100%);
        padding: 60px 0;
        text-align: center;
        margin-top: 80px;
        margin-bottom: 40px;
        border-radius: 15px;
    }
    .internship-hero h1 {
        font-size: 3rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 15px;
    }
    .internship-hero p {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 0;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .section-header h2 {
            font-size: 2rem;
        }
        .internship-info-card {
            padding: 30px;
        }
        .info-card-inner {
            flex-direction: column;
            gap: 20px;
            align-items: center;
            text-align: center;
        }
        .info-card-list li {
            padding-left: 25px;
        }
        .why-choose-section {
            padding: 50px 30px;
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
    <h1 class="mb-3 fw-bold">Explore Internship Opportunities</h1>
    <p>Find your perfect internship, whether you aspire to teach or innovate in the industry. Grow with us!</p>
</div>

<section class="internship-section">
    <div class="container">
        <!-- Teaching Internships Section -->
        <div class="section-header">
            <h2><i class="fas fa-chalkboard-teacher"></i>Teaching Internships</h2>
        </div>
        
        <!-- Teaching Internships Info Card -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="internship-info-card teaching">
                    <div class="info-card-inner">
                        <div class="info-card-icon text-primary">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="info-card-content">
                            <h5>Perfect for Passionate Trainers & Educators</h5>
                            <p>Join our team as a trainer and make an impact! This is your opportunity to:</p>
                            <ul class="info-card-list">
                                <li><strong>First Month:</strong> Earn stipend-based compensation while you adapt to our teaching style</li>
                                <li><strong>Performance-based Growth:</strong> Exceptional trainers are offered permanent positions based on performance</li>
                                <li><strong>Grow with Us:</strong> Build your career and establish yourself as an industry expert</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Teaching Internships Cards -->
        <?php if (!empty($teaching_internships)): ?>
            <div class="row g-4 justify-content-center mb-5">
                <?php foreach ($teaching_internships as $internship): ?>
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="card h-100 internship-card">
                            <div class="internship-header bg-primary text-center">
                                <div class="internship-header-content">
                                    <div class="internship-icon mx-auto">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <h3><?php echo htmlspecialchars($internship['title']); ?></h3>
                                    <div class="internship-meta">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($internship['duration']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($internship['description']); ?></p>
                                <h6 class="fw-bold mb-2">Skills Covered:</h6>
                                <div class="skills-list mb-4">
                                    <?php
                                    $skills = explode(',', $internship['skills_covered']);
                                    foreach ($skills as $skill) {
                                        echo '<span class="badge">' . htmlspecialchars(trim($skill)) . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="d-grid mt-auto">
                                    <a href="<?php echo htmlspecialchars($internship['google_form_link']); ?>" target="_blank" class="btn btn-primary apply-btn">
                                        <i class="fas fa-paper-plane me-2"></i>Apply Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted mb-5">No teaching internships available at the moment. Please check back later!</p>
        <?php endif; ?>
    </div>
</section>

<section class="internship-section" style="background-color: #f8f9fa;">
    <div class="container">
        <!-- Industrial Internships Section -->
        <div class="section-header">
            <h2><i class="fas fa-industry"></i>Industrial Internships</h2>
        </div>
        
        <!-- Industrial Internships Info Card -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="internship-info-card industrial">
                    <div class="info-card-inner">
                        <div class="info-card-icon text-success">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <div class="info-card-content">
                            <h5>Hands-On Learning for Students</h5>
                            <p>Gain real-world experience and build your professional portfolio! Our industrial internships offer:</p>
                            <ul class="info-card-list">
                                <li><strong>Project-Based Learning:</strong> Work on real-world projects relevant to your chosen technology</li>
                                <li><strong>Hands-On Practice:</strong> Get practical experience with the latest tools and technologies</li>
                                <li><strong>Industry Mentorship:</strong> Learn best practices from experienced professionals in your field</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Industrial Internships Cards -->
        <?php if (!empty($industrial_internships)): ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($industrial_internships as $internship): ?>
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="card h-100 internship-card">
                            <div class="internship-header bg-success text-center">
                                <div class="internship-header-content">
                                    <div class="internship-icon mx-auto">
                                        <i class="fas fa-industry"></i>
                                    </div>
                                    <h3><?php echo htmlspecialchars($internship['title']); ?></h3>
                                    <div class="internship-meta">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars($internship['duration']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($internship['description']); ?></p>
                                <h6 class="fw-bold mb-2">Skills Covered:</h6>
                                <div class="skills-list mb-4">
                                    <?php
                                    $skills = explode(',', $internship['skills_covered']);
                                    foreach ($skills as $skill) {
                                        echo '<span class="badge">' . htmlspecialchars(trim($skill)) . '</span>';
                                    }
                                    ?>
                                </div>
                                <div class="d-grid mt-auto">
                                    <a href="<?php echo htmlspecialchars($internship['google_form_link']); ?>" target="_blank" class="btn btn-success apply-btn">
                                        <i class="fas fa-external-link-alt me-2"></i>Apply Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No industrial internships available at the moment. Please check back later!</p>
        <?php endif; ?>
        
        <!-- Why Choose Section -->
        <div class="why-choose-section">
            <h3>Why Choose The Coding Science for Internships?</h3>
            <p class="subtitle">We connect you with real-world projects and provide expert mentorship to help you succeed.</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="why-choose-item">
                        <i class="fas fa-briefcase"></i>
                        <h5>Real-world Experience</h5>
                        <p>Gain practical skills on live projects and build a strong portfolio</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-choose-item">
                        <i class="fas fa-handshake"></i>
                        <h5>Expert Mentorship</h5>
                        <p>Learn from industry veterans and professionals with years of experience</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-choose-item">
                        <i class="fas fa-certificate"></i>
                        <h5>Certification</h5>
                        <p>Receive a recognized certificate of completion to boost your resume</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
