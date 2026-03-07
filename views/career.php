<?php
// views/career.php - Trainer Career Opportunities

require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Career - Join Our Training Team";

// Fetch trainer positions
$positions = get_trainer_positions();

require_once 'includes/header.php';
?>

<style>
    /* Main Section Styling */
    .career-section {
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
        color: #667eea;
    }
    
    /* Info Card Styling */
    .career-info-card {
        background: white;
        border-radius: 20px;
        padding: 50px;
        margin-bottom: 50px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        border-left: 6px solid #667eea;
        transition: all 0.3s ease;
        position: relative;
        z-index: 10;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
    }
    .career-info-card:hover {
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
        color: #667eea;
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
        color: #667eea;
    }
    
    /* Position Card Styling */
    .position-card {
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
    .position-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.18) !important;
    }
    .position-header {
        padding: 40px 25px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        position: relative;
        overflow: hidden;
    }
    .position-header::before {
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
    .position-card:hover .position-header::before {
        top: -30%;
        right: -30%;
    }
    .position-header-content {
        position: relative;
        z-index: 1;
        text-align: center;
    }
    .position-icon {
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
    .position-card:hover .position-icon {
        transform: scale(1.15) rotate(8deg);
        background: rgba(255,255,255,0.35);
    }
    .position-header h3 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .position-meta {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        color: rgba(255,255,255,0.95);
        font-size: 0.85rem;
        margin-top: 14px;
        font-weight: 500;
        flex-wrap: wrap;
    }
    .position-meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .position-card .card-body {
        padding: 35px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .position-card .card-body p {
        line-height: 1.7;
        color: #666;
        margin-bottom: 20px;
    }
    .position-card .card-body h6 {
        color: #333;
        margin-bottom: 15px;
        font-size: 0.98rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .requirements-list {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }
    .requirements-list li {
        padding: 8px 0 8px 25px;
        position: relative;
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    .requirements-list li::before {
        content: '▸';
        position: absolute;
        left: 0;
        color: #667eea;
        font-weight: bold;
    }
    .apply-btn {
        padding: 14px 32px;
        font-weight: 700;
        border-radius: 50px;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        margin-top: auto;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.8px;
    }
    .apply-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    /* Benefits Section */
    .benefits-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 80px 50px;
        color: white;
        margin-top: 80px;
    }
    .benefits-section h3 {
        font-size: 2.4rem;
        font-weight: 700;
        margin-bottom: 15px;
        text-align: center;
    }
    .benefits-section .subtitle {
        font-size: 1.15rem;
        opacity: 0.95;
        margin-bottom: 60px;
        text-align: center;
    }
    .benefit-item {
        text-align: center;
        padding: 25px;
        transition: all 0.3s ease;
        border-radius: 15px;
        background: rgba(255,255,255,0.1);
        padding: 35px;
    }
    .benefit-item:hover {
        transform: translateY(-12px);
        background: rgba(255,255,255,0.15);
    }
    .benefit-item i {
        font-size: 3.5rem;
        margin-bottom: 20px;
        opacity: 0.95;
    }
    .benefit-item h5 {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .benefit-item p {
        font-size: 0.98rem;
        opacity: 0.9;
    }
    
    /* Hero Section */
    .career-hero {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.1) 100%);
        padding: 80px 0;
        text-align: center;
        margin-top: 80px;
        margin-bottom: 40px;
        border-radius: 15px;
    }
    .career-hero h1 {
        font-size: 3.2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 15px;
    }
    .career-hero p {
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
        color: #667eea;
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
        .career-info-card {
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
        .benefits-section {
            padding: 50px 30px;
            margin-top: 60px;
        }
        .benefits-section h3 {
            font-size: 1.8rem;
        }
        .career-hero {
            padding: 50px 20px;
            margin-top: 60px;
        }
        .career-hero h1 {
            font-size: 2rem;
        }
        .career-hero p {
            font-size: 1rem;
        }
        .position-meta {
            font-size: 0.75rem;
        }
    }
</style>

<!-- Hero Section -->
<div class="container career-hero">
    <h1 class="mb-3 fw-bold">Build Your Career With Us</h1>
    <p>Join The Coding Science team and inspire the next generation of developers. Help shape futures and grow your expertise.</p>
</div>

<section class="career-section">
    <div class="container">
        <!-- Info Card -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-11">
                <div class="career-info-card">
                    <div class="info-card-inner">
                        <div class="info-card-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="info-card-content">
                            <h5>Become a Trainer at The Coding Science</h5>
                            <p>We are looking for passionate and experienced professionals to join our faculty. This is an opportunity to impact learning and make a real difference in students' lives.</p>
                            <ul class="info-card-list">
                                <li><strong>Competitive Compensation:</strong> Competitive base salary with performance-based incentives and bonuses</li>
                                <li><strong>Stipend Model:</strong> New trainers start with monthly stipends while they settle in, then transition to permanent positions</li>
                                <li><strong>Professional Growth:</strong> Continuous learning opportunities, skill development, and career advancement paths</li>
                                <li><strong>Flexible Work:</strong> Full-time, part-time, and freelance opportunities available based on expertise</li>
                                <li><strong>Impact:</strong> Mentor talented students and help shape the next generation of tech professionals</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Position Cards -->
        <?php if (!empty($positions)): ?>
            <div class="row g-4 justify-content-center">
                <?php foreach ($positions as $position): ?>
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="card h-100 position-card">
                            <div class="position-header">
                                <div class="position-header-content">
                                    <div class="position-icon mx-auto">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <h3><?php echo htmlspecialchars($position['title']); ?></h3>
                                    <div class="position-meta">
                                        <div class="position-meta-item">
                                            <i class="fas fa-building"></i>
                                            <span><?php echo htmlspecialchars($position['location'] ?? 'Remote'); ?></span>
                                        </div>
                                        <div class="position-meta-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo htmlspecialchars($position['employment_type']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><?php echo htmlspecialchars($position['description']); ?></p>
                                
                                <?php if ($position['minimum_experience']): ?>
                                    <h6>Experience Required:</h6>
                                    <p class="text-muted mb-3"><?php echo htmlspecialchars($position['minimum_experience']); ?>+ years</p>
                                <?php endif; ?>
                                
                                <?php if ($position['expertise_required']): ?>
                                    <h6>Required Expertise:</h6>
                                    <ul class="requirements-list mb-3">
                                        <?php
                                        $expertise = array_filter(array_map('trim', explode(',', $position['expertise_required'])));
                                        foreach ($expertise as $item) {
                                            echo '<li>' . htmlspecialchars($item) . '</li>';
                                        }
                                        ?>
                                    </ul>
                                <?php endif; ?>
                                
                                <?php if ($position['stipend_info']): ?>
                                    <div class="alert alert-info alert-sm mb-3" style="background: rgba(102, 126, 234, 0.1); border: 1px solid #667eea; color: #333; font-size: 0.9rem;">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <?php echo htmlspecialchars($position['stipend_info']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-grid mt-auto">
                                    <a href="<?php echo htmlspecialchars($position['application_link']); ?>" target="_blank" class="btn apply-btn">
                                        <i class="fas fa-paper-plane me-2"></i>Apply Now
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
                <h3>No Positions Available Now</h3>
                <p>Stay tuned! We're always looking for passionate trainers. Check back soon for exciting opportunities.</p>
            </div>
        <?php endif; ?>
        
        <!-- Benefits Section -->
        <div class="benefits-section">
            <h3>Why Join The Coding Science?</h3>
            <p class="subtitle">We invest in our team's success and growth</p>
            <div class="row g-4">
                <div class="col-md-4 col-sm-6">
                    <div class="benefit-item">
                        <i class="fas fa-star"></i>
                        <h5>Industry Recognition</h5>
                        <p>Be part of a leading EdTech organization recognized for excellence in tech education</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="benefit-item">
                        <i class="fas fa-book"></i>
                        <h5>Professional Development</h5>
                        <p>Ongoing training and resources to keep your skills current with industry trends</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="benefit-item">
                        <i class="fas fa-users-tie"></i>
                        <h5>Collaborative Team</h5>
                        <p>Work with passionate educators and experienced mentors who share your vision</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="benefit-item">
                        <i class="fas fa-chart-line"></i>
                        <h5>Career Growth</h5>
                        <p>Clear path for advancement from stipend roles to permanent positions and leadership</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="benefit-item">
                        <i class="fas fa-globe"></i>
                        <h5>Flexible Options</h5>
                        <p>Full-time, part-time, or freelance roles to fit your lifestyle and goals</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="benefit-item">
                        <i class="fas fa-heart"></i>
                        <h5>Make an Impact</h5>
                        <p>Shape young minds and help build the next generation of tech innovators</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
