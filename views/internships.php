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
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .internship-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.15) !important;
    }
    .internship-header {
        padding: 35px 20px;
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        position: relative;
    }
    .internship-header::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 0;
        right: 0;
        height: 15px;
        background: inherit;
        border-radius: 0 0 15px 15px;
        opacity: 0.1;
    }
    .internship-header.bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .internship-header.bg-success { background: linear-gradient(135deg, #2AF598 0%, #009245 100%); }
    .internship-header.bg-info { background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%); }
    .internship-header.bg-warning { background: linear-gradient(135deg, #f7b733 0%, #fc4a1a 100%); }
    .internship-header.bg-danger { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); }
    .internship-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        background: rgba(255,255,255,0.2);
        margin-bottom: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .internship-card .card-body {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .internship-card .card-body p {
        line-height: 1.6;
    }
    .skills-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .skills-list .badge {
        padding: 7px 12px;
        border-radius: 20px;
        font-weight: 500;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .apply-btn {
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        margin-top: auto;
    }
    .apply-btn:hover {
        opacity: 0.9;
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
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
    .internship-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        color: rgba(255,255,255,0.9);
        font-size: 0.95rem;
        margin-top: 10px;
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
        <?php if (!empty($teaching_internships)): ?>
            <div class="row g-4 justify-content-center mb-5">
                <?php foreach ($teaching_internships as $internship): ?>
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="card h-100 internship-card">
                            <div class="internship-header bg-primary text-center">
                                <div class="internship-icon mx-auto">
                                    <i class="fas fa-users-class"></i>
                                </div>
                                <h3 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($internship['title']); ?></h3>
                                <p class="opacity-75 mb-0"><i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($internship['duration']); ?></p>
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
        <?php if (!empty($industrial_internships)): ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($industrial_internships as $internship): ?>
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="card h-100 internship-card">
                            <div class="internship-header bg-success text-center">
                                <div class="internship-icon mx-auto">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <h3 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($internship['title']); ?></h3>
                                <p class="opacity-75 mb-0"><i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($internship['duration']); ?></p>
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
