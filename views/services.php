<?php
// views/services.php

$page_title = "Live Trainings";
require_once 'includes/header.php';

// Services List (Mirrors Flask app content_data.py)
$services = [
    [
        'id' => 1,
        'title' => 'Computer Science Engineering',
        'icon' => 'fa-microchip',
        'description' => 'Master programming fundamentals, data structures, algorithms, and computer architecture.',
        'duration' => '2-4 Months',
        'price' => '499-1499'
    ],
    [
        'id' => 2,
        'title' => 'AI & Machine Learning',
        'icon' => 'fa-robot',
        'description' => 'Build intelligent systems with neural networks, deep learning, NLP, and computer vision.',
        'duration' => '3-4 Months',
        'price' => '999-1499'
    ],
    [
        'id' => 3,
        'title' => 'Programming & DSA',
        'icon' => 'fa-code',
        'description' => 'Learn Python, Java, problem-solving, and crack coding interviews with confidence.',
        'duration' => '3-4 Months',
        'price' => '999-1499'
    ],
    [
        'id' => 4,
        'title' => 'Cloud Computing & DevOps',
        'icon' => 'fa-cloud',
        'description' => 'Master AWS, Docker, Kubernetes, CI/CD pipelines, and modern deployment practices.',
        'duration' => '3-4 Months',
        'price' => '1499'
    ]
];
?>

<section class="py-5" style="margin-top: 80px;">
    <div class="container">
        <h1 class="mb-4">Live Trainings</h1>
        <p class="lead mb-5">Explore our course categories and find the perfect learning path for your career goals.</p>

        <div class="row">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm service-card">
                        <div class="card-body text-center">
                            <i class="fas <?php echo $service['icon']; ?> fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">
                                <?php echo $service['title']; ?>
                            </h5>
                            <p class="card-text text-muted small">
                                <?php echo $service['description']; ?>
                            </p>
                            <p class="small text-muted mb-0">
                                <i class="fas fa-clock"></i>
                                <?php echo $service['duration']; ?>
                                &nbsp;•&nbsp;
                                ₹
                                <?php echo $service['price']; ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="/courses" class="btn btn-sm btn-primary">Explore Courses</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Detailed Services -->
        <div class="row mt-5">
            <div class="col-12">
                <h2 class="text-center mb-5">Why Our Live Trainings?</h2>

                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="feature-box text-center p-4 h-100">
                            <i class="fas fa-user-tie fa-2x text-primary mb-3"></i>
                            <h5>Expert Instructors</h5>
                            <p class="mb-0 small">Working professionals with real-world experience.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="feature-box text-center p-4 h-100">
                            <i class="fas fa-project-diagram fa-2x text-success mb-3"></i>
                            <h5>Real Projects</h5>
                            <p class="mb-0 small">Build portfolio-ready projects.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="feature-box text-center p-4 h-100">
                            <i class="fas fa-briefcase fa-2x text-info mb-3"></i>
                            <h5>Career Support</h5>
                            <p class="mb-0 small">Resume & interview prep included.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="feature-box text-center p-4 h-100">
                            <i class="fas fa-users fa-2x text-danger mb-3"></i>
                            <h5>Community</h5>
                            <p class="mb-0 small">Connect with peers & professionals.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Technology Stack -->
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="text-center mb-4">Technologies We Teach</h2>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h6 class="text-primary"><i class="fas fa-code"></i> Frontend</h6>
                        <p class="small">HTML, CSS, JavaScript, React, Vue, Bootstrap</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-success"><i class="fas fa-server"></i> Backend</h6>
                        <p class="small">Node.js, Python, PHP, Flask, Django, Express</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-info"><i class="fas fa-database"></i> Database</h6>
                        <p class="small">MySQL, MongoDB, PostgreSQL, Firebase</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-warning"><i class="fas fa-cloud"></i> Cloud & DevOps</h6>
                        <p class="small">AWS, Azure, Docker, Kubernetes, Jenkins</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-danger"><i class="fas fa-brain"></i> Data & AI</h6>
                        <p class="small">Python, TensorFlow, Pandas, Scikit-learn, ML</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-primary"><i class="fas fa-tools"></i> Tools & Platforms</h6>
                        <p class="small">Git, GitHub, VS Code, Postman, Linux</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto text-center">
                <h3>Ready to Start Your Live Training?</h3>
                <p class="lead mb-4">Enroll in a foundational live cohort and level up in 3 months.</p>
                <a href="/courses" class="btn btn-primary btn-lg">Explore Live Trainings</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>