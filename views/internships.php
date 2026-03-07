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
        margin-bottom: 20px;
    }
    .internship-card .card-body h6 {
        color: #333;
        margin-bottom: 12px;
        font-size: 0.95rem;
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
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
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
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #343a40;
        margin-bottom: 40px;
        text-transform: capitalize;
        letter-spacing: -0.5px;
    }
    .lead-text {
        font-size: 1.15rem;
        color: #6c757d;
    }
    .internship-info-card {
        border: 0;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .internship-info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    .internship-info-card.primary {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-left: 5px solid #667eea;
    }
    .internship-info-card.success {
        background: linear-gradient(135deg, rgba(42, 245, 152, 0.1) 0%, rgba(0, 146, 69, 0.1) 100%);
        border-left: 5px solid #2AF598;
    }
    .internship-info-card h5 {
        color: #333;
        font-weight: 700;
    }
    .internship-info-card .fa-3x {
        opacity: 0.9;
    }
    .internship-info-card ul li {
        color: #555;
        line-height: 1.8;
        margin-bottom: 8px;
    }
</style>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-3 fw-bold">Explore Internship Opportunities</h1>
            <p class="lead lead-text">Find your perfect internship, whether you aspire to teach or innovate in the industry. Grow with us!</p>
        </div>

        <!-- Teaching Internships Section -->
        <h2 class="text-center section-title mb-4"><i class="fas fa-chalkboard-teacher me-2"></i>Teaching Internships</h2>
        
        <!-- Teaching Internships Info Card -->
        <div class="row g-4 justify-content-center mb-5">
            <div class="col-md-8">
                <div class="alert alert-info border-0 rounded-3 internship-info-card primary">
                    <div class="row align-items-center p-2">
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                        </div>
                        <div class="col">
                            <h5 class="mb-2"><strong>Perfect for Passionate Trainers & Educators</strong></h5>
                            <p class="mb-2">Join our team as a trainer and make an impact! This is your opportunity to:</p>
                            <ul class="mb-0 small">
                                <li><strong>First Month:</strong> Earn stipend-based compensation while you adapt to our teaching style</li>
                                <li><strong>Performance-based Growth:</strong> Exceptional trainers are offered permanent positions based on performance</li>
                                <li><strong>Grow with Us:</strong> Build your career and establish yourself as an industry expert</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

        <!-- Industrial Internships Section -->
        <h2 class="text-center section-title mb-4 mt-5"><i class="fas fa-industry me-2"></i>Industrial Internships</h2>
        
        <!-- Industrial Internships Info Card -->
        <div class="row g-4 justify-content-center mb-5">
            <div class="col-md-8">
                <div class="alert alert-success border-0 rounded-3 internship-info-card success">
                    <div class="row align-items-center p-2">
                        <div class="col-auto">
                            <i class="fas fa-laptop-code fa-3x text-success"></i>
                        </div>
                        <div class="col">
                            <h5 class="mb-2"><strong>Hands-On Learning for Students</strong></h5>
                            <p class="mb-2">Gain real-world experience and build your professional portfolio! Our industrial internships offer:</p>
                            <ul class="mb-0 small">
                                <li><strong>Project-Based Learning:</strong> Work on real-world projects relevant to your chosen technology</li>
                                <li><strong>Hands-On Practice:</strong> Get practical experience with the latest tools and technologies</li>
                                <li><strong>Industry Mentorship:</strong> Learn best practices from experienced professionals in your field</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

        <div class="mt-5 text-center bg-light p-5 rounded">
            <h3>Why Choose The Coding Science for Internships?</h3>
            <p class="mb-4">We connect you with real-world projects and provide expert mentorship.</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                    <h5>Real-world Experience</h5>
                    <p class="text-muted mb-0">Gain practical skills on live projects</p>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-handshake fa-3x text-success mb-3"></i>
                    <h5>Expert Mentorship</h5>
                    <p class="text-muted mb-0">Learn from industry veterans and professionals</p>
                </div>
                <div class="col-md-4">
                    <i class="fas fa-certificate fa-3x text-warning mb-3"></i>
                    <h5>Certification</h5>
                    <p class="text-muted mb-0">Receive a certificate of completion</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
