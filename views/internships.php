<?php
// views/internships.php

$page_title = "Internships";
require_once 'includes/header.php';

// Internships List (Mirrors Flask app content_data.py)
$internships = [
    [
        'id' => 1,
        'role' => 'Web Development Intern',
        'company' => 'School of Technology and AI Innovations',
        'duration' => '3 Months',
        'location' => 'Remote',
        'stipend' => 999,
        'description' => 'Build real-world websites with React, Node.js & MongoDB. Gain hands-on experience with modern web technologies and industry best practices.'
    ],
    [
        'id' => 2,
        'role' => 'Python Development Intern',
        'company' => 'School of Technology and AI Innovations',
        'duration' => '3 Months',
        'location' => 'Remote',
        'stipend' => 999,
        'description' => 'Master backend development with Python. Build APIs, manage databases, and work on real-world projects with experienced mentors.'
    ],
    [
        'id' => 3,
        'role' => 'Data Science and AI Intern',
        'company' => 'School of Technology and AI Innovations',
        'duration' => '3 Months',
        'location' => 'Remote',
        'stipend' => 999,
        'description' => 'Work with real datasets and build ML models. Learn machine learning, deep learning, and solve real-world AI problems.'
    ]
];
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-3">Internship Opportunities</h1>
            <p class="lead text-muted">Kickstart your career with our hands-on internship programs.</p>
        </div>

        <div class="row">
            <?php foreach ($internships as $internship): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-lift">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-primary-light text-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-briefcase fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="card-title fw-bold mb-1">
                                        <?php echo $internship['role']; ?>
                                    </h5>
                                    <p class="text-muted small mb-0">
                                        <?php echo $internship['company']; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <span class="badge bg-light text-dark border me-2"><i class="fas fa-clock"></i>
                                    <?php echo $internship['duration']; ?>
                                </span>
                                <span class="badge bg-light text-dark border"><i class="fas fa-map-marker-alt"></i>
                                    <?php echo $internship['location']; ?>
                                </span>
                            </div>

                            <p class="card-text text-muted">
                                <?php echo $internship['description']; ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div>
                                    <small class="text-muted d-block">Stipend</small>
                                    <span class="fw-bold text-success">₹
                                        <?php echo $internship['stipend']; ?>/mo
                                    </span>
                                </div>
                                <a href="/apply-internship/<?php echo $internship['id']; ?>"
                                    class="btn btn-outline-primary rounded-pill px-4">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-5 text-center bg-light p-5 rounded">
            <h3>Don't see what you're looking for?</h3>
            <p class="mb-4">We are always looking for talented individuals. Send us your resume.</p>
            <a href="/contact" class="btn btn-primary">Contact Us</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>