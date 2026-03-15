<?php
// views/career.php - Technical Trainer Career Page

require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = "Career - Join Our Team as a Technical Trainer | The Coding Science";

require_once 'includes/header.php';

// Google Form application link
$apply_link = "https://docs.google.com/forms/d/e/1FAIpQLSc7OK-eGBEV-PWb4LJflIJJF2V1Zk_s_-p0kbiwrwgcibLt6w/viewform?usp=sharing&ouid=102780806115090907943"; 

// Fetch specific trainer positions from database
$positions = get_trainer_positions();
?>

<style>
    :root {
        --primary: #667eea;
        --secondary: #764ba2;
        --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --text-dark: #1a1a1a;
        --text-light: #666;
        --bg-light: #f8f9fa;
    }
    
    .career-page-wrapper {
        font-family: 'Inter', sans-serif;
        background-color: #fafbfc;
    }

    /* Hero Section */
    .career-hero {
        background: var(--gradient);
        padding: 140px 0 100px;
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .career-hero::before {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        top: -100px;
        left: -100px;
    }
    .career-hero::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
        bottom: -50px;
        right: -50px;
    }
    .career-hero .container {
        position: relative;
        z-index: 2;
    }
    .hero-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 0.95rem;
        font-weight: 600;
        letter-spacing: 1px;
        margin-bottom: 25px;
        backdrop-filter: blur(10px);
        text-transform: uppercase;
    }
    .career-hero h1 {
        font-size: 3.8rem;
        font-weight: 800;
        margin-bottom: 25px;
        letter-spacing: -1px;
        line-height: 1.2;
    }
    .career-hero p {
        font-size: 1.25rem;
        opacity: 0.95;
        max-width: 700px;
        margin: 0 auto 40px;
        line-height: 1.6;
    }
    
    /* Buttons */
    .btn-apply-primary {
        background: #fff;
        color: var(--secondary);
        padding: 16px 45px;
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }
    .btn-apply-primary:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.25);
        color: var(--primary);
    }
    
    .btn-apply-secondary {
        background: var(--gradient);
        color: #fff;
        padding: 16px 45px;
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        border: none;
    }
    .btn-apply-secondary:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
        color: #fff;
    }

    /* Common Sections */
    .section-padding {
        padding: 100px 0;
    }
    .bg-white-section { background: #fff; }
    
    .section-title {
        text-align: center;
        margin-bottom: 60px;
    }
    .section-title span {
        color: var(--primary);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 0.9rem;
        display: block;
        margin-bottom: 15px;
    }
    .section-title h2 {
        font-size: 3rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 20px;
        line-height: 1.2;
    }
    .section-title p {
        color: var(--text-light);
        font-size: 1.15rem;
        max-width: 650px;
        margin: 0 auto;
        line-height: 1.7;
    }
    
    /* About Role Section */
    .role-image-wrapper {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        height: 100%;
        min-height: 400px;
        background: var(--gradient);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .role-image-wrapper i {
        font-size: 8rem;
        color: rgba(255,255,255,0.9);
    }
    .role-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .role-list li {
        display: flex;
        align-items: flex-start;
        margin-bottom: 30px;
        background: #fff;
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }
    .role-list li:hover {
        transform: translateX(10px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.06);
        border-color: rgba(102, 126, 234, 0.2);
    }
    .role-list li .icon-wrapper {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.8rem;
        margin-right: 20px;
        flex-shrink: 0;
    }
    .role-list li h4 {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text-dark);
    }
    .role-list li p {
        color: var(--text-light);
        margin: 0;
        line-height: 1.6;
        font-size: 1.05rem;
    }

    /* Domains Grid */
    .domain-card {
        background: #fff;
        border-radius: 24px;
        padding: 40px 30px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid rgba(102, 126, 234, 0.1);
        height: 100%;
        position: relative;
        overflow: hidden;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .domain-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--gradient);
        z-index: -1;
        opacity: 0;
        transition: all 0.4s ease;
    }
    .domain-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.25);
        border-color: transparent;
    }
    .domain-card:hover::before {
        opacity: 1;
    }
    .domain-icon {
        width: 85px;
        height: 85px;
        background: rgba(102, 126, 234, 0.08);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--primary);
        margin: 0 auto 25px;
        transition: all 0.4s ease;
    }
    .domain-card:hover .domain-icon {
        background: rgba(255,255,255,0.2);
        color: #fff;
        transform: scale(1.1) rotate(5deg);
    }
    .domain-card h4 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0;
        transition: all 0.4s ease;
        line-height: 1.4;
    }
    .domain-card:hover h4 {
        color: #fff;
    }

    /* Attributes Layout: Who Can Apply & Work Type */
    .attributes-section {
        background: #fff;
        position: relative;
    }
    .attr-box {
        background: #fafbfc;
        border-radius: 24px;
        padding: 50px 40px;
        height: 100%;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .attr-box h3 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 30px;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .attr-box h3 i {
        color: var(--primary);
    }
    .pill-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .feature-pill {
        background: #fff;
        border-radius: 50px;
        padding: 16px 28px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        font-weight: 600;
        font-size: 1.05rem;
        color: var(--text-dark);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .feature-pill:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 30px rgba(102, 126, 234, 0.15);
        border-color: rgba(102, 126, 234, 0.4);
        color: var(--primary);
    }
    .feature-pill i {
        color: var(--primary);
        font-size: 1.3rem;
    }

    /* Final CTA Section */
    .cta-container {
        padding: 0 20px;
        margin-bottom: 80px;
    }
    .cta-section {
        background: var(--gradient);
        padding: 90px 40px;
        text-align: center;
        color: white;
        border-radius: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 30px 60px rgba(102, 126, 234, 0.3);
    }
    .cta-section::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml;utf8,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" stroke="rgba(255,255,255,0.1)" stroke-width="2" fill="none"/></svg>') repeat;
        opacity: 0.5;
    }
    .cta-content {
        position: relative;
        z-index: 2;
    }
    .cta-section h2 {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 25px;
        line-height: 1.1;
    }
    .cta-section p {
        font-size: 1.3rem;
        opacity: 0.95;
        margin-bottom: 45px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    @media (max-width: 991px) {
        .career-hero h1 { font-size: 3rem; }
        .section-title h2 { font-size: 2.5rem; }
        .role-image-wrapper { min-height: 300px; margin-bottom: 40px; }
        .cta-section h2 { font-size: 2.8rem; }
    }
    @media (max-width: 768px) {
        .career-hero { padding: 120px 20px 80px; }
        .career-hero h1 { font-size: 2.5rem; }
        .career-hero p { font-size: 1.1rem; }
        .section-padding { padding: 70px 0; }
        .section-title h2 { font-size: 2.2rem; }
        .attr-box { padding: 40px 25px; }
        .cta-section { padding: 60px 20px; border-radius: 25px; }
        .cta-section h2 { font-size: 2.2rem; }
        .btn-apply-primary, .btn-apply-secondary { font-size: 1rem; padding: 14px 35px; }
    }
    /* Dynamic Position Card Styling */
    .specific-positions-section {
        background: #fdfdfe;
    }
    .pos-card {
        background: #fff;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .pos-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.12);
        border-color: rgba(102, 126, 234, 0.2);
    }
    .pos-card-header {
        padding: 35px 30px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03) 0%, rgba(118, 75, 162, 0.03) 100%);
        border-bottom: 1px solid rgba(0,0,0,0.02);
        position: relative;
    }
    .pos-card-header h3 {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 12px;
    }
    .pos-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        color: var(--text-light);
        font-size: 0.9rem;
        font-weight: 600;
    }
    .pos-meta span {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pos-meta i {
        color: var(--primary);
    }
    .pos-card-body {
        padding: 30px;
        flex-grow: 1;
        color: var(--text-light);
        line-height: 1.6;
    }
    .pos-card-body h6 {
        color: var(--text-dark);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.85rem;
        margin-bottom: 15px;
        margin-top: 20px;
    }
    .pos-card-body h6:first-child { margin-top: 0; }
    
    .req-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .req-list li {
        padding-left: 20px;
        position: relative;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    .req-list li::before {
        content: '\f0da';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        color: var(--primary);
    }
    .pos-card-footer {
        padding: 0 30px 35px;
    }
    
    /* Empty State */
    .empty-positions {
        text-align: center;
        padding: 40px;
        background: rgba(102, 126, 234, 0.05);
        border-radius: 24px;
        border: 2px dashed rgba(102, 126, 234, 0.2);
    }
    .empty-positions i {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 15px;
        opacity: 0.5;
    }
</style>

<div class="career-page-wrapper">
    <!-- Hero Section -->
    <section class="career-hero">
        <div class="container">
            <div class="hero-badge"><i class="fas fa-rocket me-2"></i> We are hiring!</div>
            <h1>Join Our Team as a Technical Trainer</h1>
            <p>Inspire the next generation of developers and technologists. Help shape futures, impart practical skills, and grow your own expertise with The Coding Science.</p>
            <a href="<?php echo htmlspecialchars($apply_link); ?>" target="_blank" class="btn-apply-primary mt-3">
                Apply Now <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <!-- Specific Job Openings -->
    <?php if (!empty($positions)): ?>
    <section class="section-padding specific-positions-section">
        <div class="container">
            <div class="section-title">
                <span>Current Openings</span>
                <h2>Specific Opportunities</h2>
                <p>Explore our specialized trainer roles currently open for applications. We are looking for experts to join our core team.</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <?php foreach ($positions as $position): ?>
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="pos-card">
                            <div class="pos-card-header">
                                <h3><?php echo htmlspecialchars($position['title']); ?></h3>
                                <div class="pos-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($position['location'] ?? 'Remote'); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($position['employment_type']); ?></span>
                                </div>
                            </div>
                            <div class="pos-card-body">
                                <div class="mb-4 text-muted"><?php echo markdown_to_html($position['description']); ?></div>
                                
                                <?php if ($position['minimum_experience']): ?>
                                    <h6>Experience Required</h6>
                                    <ul class="req-list mb-3">
                                        <li>At least <?php echo htmlspecialchars($position['minimum_experience']); ?>+ years of relevant experience</li>
                                    </ul>
                                <?php endif; ?>
                                
                                <?php if ($position['expertise_required']): ?>
                                    <h6>Key Expertise</h6>
                                    <ul class="req-list mb-3">
                                        <?php
                                        $expertise = array_filter(array_map('trim', explode(',', $position['expertise_required'])));
                                        foreach ($expertise as $item) {
                                            echo '<li>' . htmlspecialchars($item) . '</li>';
                                        }
                                        ?>
                                    </ul>
                                <?php endif; ?>

                                <?php if (!empty($position['requirement_details'])): ?>
                                    <h6>Detailed Requirements</h6>
                                    <div class="mb-3 ps-3 border-start" style="font-size: 0.95rem; border-color: var(--primary) !important;">
                                        <?php echo nl2br(htmlspecialchars($position['requirement_details'])); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($position['stipend_info'])): ?>
                                    <div class="mt-4 p-3 rounded-4" style="background: rgba(102, 126, 234, 0.08); border: 1px dashed var(--primary);">
                                        <h6 class="mb-2"><i class="fas fa-money-bill-wave me-2 text-primary"></i>Stipend & Benefits</h6>
                                        <p class="mb-0" style="font-size: 0.95rem; color: var(--text-dark);"><?php echo nl2br(htmlspecialchars($position['stipend_info'])); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($position['growth_opportunities'])): ?>
                                    <h6 class="mt-4">Growth & Development</h6>
                                    <p class="mb-0" style="font-size: 0.95rem;"><?php echo nl2br(htmlspecialchars($position['growth_opportunities'])); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="pos-card-footer">
                                <a href="<?php echo htmlspecialchars($position['application_link'] ?? $apply_link); ?>" target="_blank" class="btn-apply-secondary w-100 justify-content-center">
                                    Quick Apply <i class="fas fa-paper-plane ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- About the Role -->
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <div class="role-image-wrapper">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
                <div class="col-lg-7 ps-lg-5">
                    <div class="section-title text-start mb-5">
                        <span>About The Role</span>
                        <h2>Shape the Future of Tech Education</h2>
                        <p>As a Technical Trainer, you will play a crucial role in delivering high-quality education and mentoring students to achieve their career goals.</p>
                    </div>
                    
                    <ul class="role-list">
                        <li>
                            <div class="icon-wrapper"><i class="fas fa-users"></i></div>
                            <div>
                                <h4>Teaching Students</h4>
                                <p>Deliver engaging and insightful lectures on industry-standard technologies and best practices.</p>
                            </div>
                        </li>
                        <li>
                            <div class="icon-wrapper"><i class="fas fa-hands-helping"></i></div>
                            <div>
                                <h4>Mentoring & Guidance</h4>
                                <p>Provide one-on-one mentorship, resolve doubts, and guide students through complex concepts.</p>
                            </div>
                        </li>
                        <li>
                            <div class="icon-wrapper"><i class="fas fa-laptop-code"></i></div>
                            <div>
                                <h4>Conducting Practical Sessions</h4>
                                <p>Lead hands-on coding sessions, project building, and essential real-world application building.</p>
                            </div>
                        </li>
                        <li>
                            <div class="icon-wrapper"><i class="fas fa-book-open"></i></div>
                            <div>
                                <h4>Preparing Learning Materials</h4>
                                <p>Design and update course curriculums, assignments, and study materials aligned with current industry trends.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Domains We Are Hiring For -->
    <section class="section-padding">
        <div class="container">
            <div class="section-title">
                <span>Areas of Expertise</span>
                <h2>Domains We Are Hiring For</h2>
                <p>We are looking for passionate subject matter experts in various cutting-edge technological domains.</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="domain-card">
                        <div class="domain-icon"><i class="fab fa-python"></i></div>
                        <h4>Python<br>Programming</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="domain-card">
                        <div class="domain-icon"><i class="fas fa-layer-group"></i></div>
                        <h4>Full Stack Web<br>Development</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="domain-card">
                        <div class="domain-icon"><i class="fas fa-brain"></i></div>
                        <h4>AI & Machine<br>Learning</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="domain-card">
                        <div class="domain-icon"><i class="fas fa-user-secret"></i></div>
                        <h4>Ethical Hacking &<br>Pen Testing</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="domain-card">
                        <div class="domain-icon"><i class="fas fa-chart-pie"></i></div>
                        <h4>Data Science &<br>Analytics</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="domain-card">
                        <div class="domain-icon"><i class="fas fa-comment-dots"></i></div>
                        <h4>Prompt<br>Engineering</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="domain-card">
                        <div class="domain-icon"><i class="fas fa-microchip"></i></div>
                        <h4>AI<br>Tools</h4>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="domain-card">
                        <div class="domain-icon"><i class="fas fa-square-root-alt"></i></div>
                        <h4>Mathematical<br>Aptitude</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Who Can Apply & Work Type -->
    <section class="section-padding bg-white-section attributes-section">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="attr-box border-0">
                        <h3><i class="fas fa-user-check"></i> Who Can Apply?</h3>
                        <p class="mb-4 text-muted fs-5">We welcome individuals from diverse professional backgrounds who have a genuine passion for teaching and sharing knowledge.</p>
                        <div class="pill-grid">
                            <div class="feature-pill"><i class="fas fa-chalkboard-teacher"></i> Trainers</div>
                            <div class="feature-pill"><i class="fas fa-code"></i> Developers</div>
                            <div class="feature-pill"><i class="fas fa-laptop-house"></i> Freelancers</div>
                            <div class="feature-pill"><i class="fas fa-user-tie"></i> Industry Professionals</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="attr-box border-0">
                        <h3><i class="fas fa-briefcase"></i> Work Type</h3>
                        <p class="mb-4 text-muted fs-5">We offer flexible working arrangements to suit your schedule and lifestyle preferences.</p>
                        <div class="pill-grid">
                            <div class="feature-pill"><i class="fas fa-business-time"></i> Part-Time</div>
                            <div class="feature-pill"><i class="fas fa-sun"></i> Full-Time</div>
                            <div class="feature-pill"><i class="fas fa-handshake"></i> Freelance</div>
                            <div class="feature-pill"><i class="fas fa-wifi"></i> Remote</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <div class="cta-container">
        <div class="container">
            <section class="cta-section">
                <div class="cta-content">
                    <h2>Ready to Make an Impact?</h2>
                    <p>Take the next step in your career. Become part of The Coding Science trainer team and empower students with high-demand tech skills.</p>
                    <a href="<?php echo htmlspecialchars($apply_link); ?>" target="_blank" class="btn-apply-primary btn-lg mt-2">
                        Apply Now <i class="fas fa-rocket ms-2"></i>
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
